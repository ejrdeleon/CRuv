import L from 'leaflet';

window.L = L;

// Fix Leaflet's default icon paths with Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

// Santa Cruz, Laguna official center and municipal + environs bounds
const SANTA_CRUZ_CENTER = [14.2816, 121.4146];
const SANTA_CRUZ_BOUNDS = L.latLngBounds(
    [14.15, 121.25],
    [14.42, 121.58]
);

class CivicSpatialMapController {
    constructor() {
        this.instances = new Map();
        this._bindGlobalEvents();
    }

    resolveMarkerColor(report) {
        if (report.priority === 'urgent' || report.status === 'rejected') {
            return '#e11d48'; // Critical Hazard / Coral Red
        }
        if (report.status === 'resolved') {
            return '#059669'; // Resolved / Green
        }
        if (report.priority === 'low' && report.status === 'submitted') {
            return '#db2777'; // Low Priority / Soft Pink
        }
        return '#d97706'; // In Progress / Active Amber
    }

    escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    mount(containerId, initialMarkers = []) {
        const container = document.getElementById(containerId);
        if (!container) {
            return null;
        }

        // Clean up existing instance if Livewire replaced the DOM node
        const existing = this.instances.get(containerId);
        if (existing) {
            if (existing.container === container && existing.map) {
                this.renderMarkers(containerId, initialMarkers);
                existing.map.invalidateSize();
                return existing;
            }
            this.unmount(containerId);
        }

        // Guard if Leaflet had attached _leaflet_id to this DOM element previously
        if (container._leaflet_id) {
            container._leaflet_id = null;
            container.innerHTML = '';
        }

        const map = L.map(container, {
            center: SANTA_CRUZ_CENTER,
            zoom: 15,
            minZoom: 12,
            maxZoom: 19,
            maxBounds: SANTA_CRUZ_BOUNDS,
            maxBoundsViscosity: 0.85,
            zoomControl: true,
            scrollWheelZoom: true,
        });

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors',
        }).addTo(map);

        const markersLayer = L.layerGroup().addTo(map);

        const resizeObserver = new ResizeObserver(() => {
            if (container.offsetWidth > 0 && container.offsetHeight > 0) {
                map.invalidateSize({ pan: false });
            }
        });
        resizeObserver.observe(container);

        const state = {
            containerId,
            container,
            map,
            markersLayer,
            markersById: {},
            resizeObserver,
        };

        this.instances.set(containerId, state);
        this.renderMarkers(containerId, initialMarkers);

        // Ensure accurate tile sizing after initial flex/grid layout & transitions
        requestAnimationFrame(() => map.invalidateSize());
        setTimeout(() => map.invalidateSize(), 150);
        setTimeout(() => map.invalidateSize(), 350);

        return state;
    }

    unmount(containerId) {
        const existing = this.instances.get(containerId);
        if (!existing) {
            return;
        }
        try {
            if (existing.resizeObserver) {
                existing.resizeObserver.disconnect();
            }
            if (existing.map) {
                existing.map.remove();
            }
        } catch (_) {
            // Ignore teardown errors on detached nodes
        }
        this.instances.delete(containerId);
    }

    renderMarkers(containerId, reports = []) {
        const state = this.instances.get(containerId);
        if (!state || !state.map) {
            return;
        }

        state.markersLayer.clearLayers();
        state.markersById = {};

        if (!Array.isArray(reports)) {
            return;
        }

        reports.forEach((r) => {
            const lat = Number.parseFloat(r?.lat);
            const lng = Number.parseFloat(r?.lng);

            // Safely skip reports with missing, null, or invalid coordinates
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            const color = this.resolveMarkerColor(r);

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:16px; height:16px; border-radius:50%; border:2.5px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8],
                popupAnchor: [0, -8],
            });

            const popupHtml = `
                <div class="civic-map-popup">
                    <div class="civic-map-popup-ref">${this.escapeHtml(r.ref)}</div>
                    <div class="civic-map-popup-title">${this.escapeHtml(r.title)}</div>
                    <div class="civic-map-popup-meta">
                        ${this.escapeHtml(r.category)} &bull; <strong style="color:${color};">${this.escapeHtml(r.status_label)}</strong>
                    </div>
                    ${r.address ? `<div class="civic-map-popup-address">${this.escapeHtml(r.address)}</div>` : ''}
                    ${r.url ? `<a href="${this.escapeHtml(r.url)}" class="civic-map-popup-link">Inspect Case Dossier &rarr;</a>` : ''}
                </div>
            `;

            const marker = L.marker([lat, lng], { icon })
                .addTo(state.markersLayer)
                .bindPopup(popupHtml);

            marker.on('click', () => {
                const wireRoot = state.container.closest('[wire\\:id]');
                if (wireRoot && window.Livewire) {
                    const comp = window.Livewire.find(wireRoot.getAttribute('wire:id'));
                    if (comp) {
                        comp.call('selectReport', r.id);
                    }
                }
                const rowEl = document.querySelector(`[data-report-row-id="${r.id}"]`);
                if (rowEl) {
                    rowEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });

            state.markersById[r.id] = marker;
        });
    }

    panToReport(detail) {
        const payload = Array.isArray(detail) ? detail[0] : detail;
        if (!payload) {
            return;
        }

        const lat = Number.parseFloat(payload.lat);
        const lng = Number.parseFloat(payload.lng);
        const id = payload.id;

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        this.instances.forEach((state) => {
            if (!document.body.contains(state.container)) {
                this.unmount(state.containerId);
                return;
            }
            state.map.invalidateSize();
            state.map.flyTo([lat, lng], 16, { duration: 0.45 });
            if (id && state.markersById[id]) {
                state.markersById[id].openPopup();
            }
        });
    }

    invalidateAll() {
        this.instances.forEach((state) => {
            if (document.body.contains(state.container)) {
                state.map.invalidateSize();
            }
        });
    }

    _bindGlobalEvents() {
        window.addEventListener('pan-admin-map', (event) => {
            this.panToReport(event.detail);
        });

        window.addEventListener('sync-admin-map-markers', (event) => {
            const payload = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            const markers = payload?.markers ?? [];
            this.instances.forEach((state) => {
                if (document.body.contains(state.container)) {
                    this.renderMarkers(state.containerId, markers);
                    state.map.invalidateSize();
                }
            });
        });

        window.addEventListener('resize', () => {
            this.invalidateAll();
        });
    }
}

window.CivicSpatialMap = new CivicSpatialMapController();
window.dispatchEvent(new CustomEvent('civic-map-ready'));
