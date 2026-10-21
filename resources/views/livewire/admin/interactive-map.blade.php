<div class="space-y-4">
    <!-- Top Filter Controls -->
    <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900">Geospatial Incident Map</h1>
                <p class="text-xs text-slate-500">Visual mapping of community infrastructure damage and hazard reports.</p>
            </div>

            <!-- Legend indicators -->
            <div class="flex items-center gap-3 text-xs flex-wrap">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Urgent</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Under Review</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Verified</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-teal-800"></span> In Progress</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Resolved</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 pt-2 border-t border-slate-100 text-xs">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference or title..."
                class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">

            <select wire:model.live="categoryFilter" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs bg-white focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs bg-white focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                <option value="">All Statuses</option>
                <option value="submitted">Submitted</option>
                <option value="under_review">Under Review</option>
                <option value="verified">Verified</option>
                <option value="assigned">Assigned</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>

            <select wire:model.live="priorityFilter" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs bg-white focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                <option value="">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>

            <button type="button" wire:click="resetFilters" class="px-2.5 py-1.5 rounded-md bg-white hover:bg-slate-50 text-slate-700 font-semibold border border-slate-300 transition text-center text-xs">
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Map & Side Details Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Interactive Map Container -->
        <div class="lg:col-span-2 bg-white rounded-lg p-3 border border-slate-200 shadow-xs" wire:ignore>
            <div class="rounded-md border border-slate-300 overflow-hidden relative">
                <div id="full-interactive-map" style="height: 560px; width: 100%;"></div>
            </div>
        </div>

        <!-- Sidebar List / Selected Report Detail -->
        <div class="space-y-3">
            @if($selectedReport)
                <div class="bg-white rounded-lg p-4 border border-teal-600 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="text-xs font-mono font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                            {{ $selectedReport->reference_number }}
                        </span>
                        <button type="button" wire:click="clearSelectedReport" class="text-slate-400 hover:text-slate-700 text-base font-bold leading-none">×</button>
                    </div>

                    @if($selectedReport->images->first())
                        <div class="aspect-video rounded-md overflow-hidden border border-slate-200 bg-slate-100">
                            <img src="{{ $selectedReport->images->first()->url }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="space-y-0.5">
                        <h3 class="text-xs font-bold text-slate-900 leading-snug">{{ $selectedReport->title }}</h3>
                        <p class="text-[11px] text-slate-500">{{ $selectedReport->category->name }}</p>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded border border-slate-200 text-xs space-y-0.5 text-slate-700">
                        <p class="font-medium truncate">{{ $selectedReport->address_text }}</p>
                        <p class="text-slate-500 font-mono text-[11px]">{{ $selectedReport->latitude }}, {{ $selectedReport->longitude }}</p>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold border border-slate-200 bg-slate-100 text-slate-800">
                            {{ $selectedReport->status_label }}
                        </span>
                        <span class="font-semibold uppercase text-[10px] {{ $selectedReport->priority === 'urgent' ? 'text-rose-700 font-bold' : 'text-slate-500' }}">
                            {{ $selectedReport->priority }} Priority
                        </span>
                    </div>

                    <a href="{{ route('admin.reports.show', $selectedReport->id) }}"
                        class="block w-full py-2 px-3 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs text-center shadow-xs transition">
                        Open Report Dossier →
                    </a>
                </div>
            @endif

            <!-- List of Pins in this View -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-xs space-y-0 max-h-[560px] overflow-y-auto">
                <div class="p-3 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Pins in View ({{ $reports->count() }})</h3>
                    <span class="text-[10px] text-slate-400">Select to highlight</span>
                </div>

                @if($reports->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-6">No matching pins found on map.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($reports as $r)
                            <div wire:click="selectReport({{ $r->id }})"
                                class="p-3 text-xs cursor-pointer transition {{ $selectedReportId === $r->id ? 'bg-teal-50/60 font-medium' : 'hover:bg-slate-50' }}">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="font-mono text-[10px] font-semibold text-slate-500">{{ $r->reference_number }}</span>
                                    <span class="text-[10px] uppercase font-semibold {{ $r->priority === 'urgent' ? 'text-rose-700' : 'text-slate-500' }}">
                                        {{ $r->priority }}
                                    </span>
                                </div>
                                <p class="font-semibold text-slate-900 truncate">{{ $r->title }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $r->address_text }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let fullMap = null;
    let markersLayer = null;

    document.addEventListener('DOMContentLoaded', function() {
        initFullInteractiveMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initFullInteractiveMap();
    });

    function initFullInteractiveMap() {
        const container = document.getElementById('full-interactive-map');
        if (!container) return;

        if (fullMap) {
            fullMap.remove();
            fullMap = null;
        }

        const markers = @json($mapMarkers);

        fullMap = L.map('full-interactive-map', {
            zoomControl: true
        }).setView([14.1815, 121.2845], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(fullMap);

        markers.forEach(m => {
            let color = '#0f766e';
            if (m.priority === 'urgent') color = '#e11d48';
            else if (m.status === 'resolved') color = '#059669';
            else if (m.status === 'submitted' || m.status === 'under_review') color = '#d97706';
            else if (m.status === 'verified' || m.status === 'assigned') color = '#2563eb';

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:18px; height:18px; border-radius:50%; border:2px solid #ffffff; box-shadow:0 1px 3px rgba(0,0,0,0.35);"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9]
            });

            const marker = L.marker([m.lat, m.lng], { icon: icon }).addTo(fullMap);

            marker.on('click', function() {
                if (window.Livewire) {
                    const comp = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    if (comp) {
                        comp.call('selectReport', m.id);
                    }
                }
            });

            marker.bindPopup(`
                <div style="font-family:sans-serif; min-width:180px;">
                    <div style="font-family:monospace; font-size:10px; font-weight:bold; color:#64748b;">${m.ref}</div>
                    <div style="font-size:12px; font-weight:bold; margin-top:2px; color:#0f172a;">${m.title}</div>
                    <div style="font-size:11px; color:#475569; margin:4px 0;">${m.category} • <b style="color:${color}">${m.status_label}</b></div>
                    <a href="${m.url}" style="display:inline-block; margin-top:4px; padding:3px 8px; background:#0f766e; color:#ffffff; font-size:11px; font-weight:bold; border-radius:4px; text-decoration:none;">Inspect Dossier</a>
                </div>
            `);
        });
    }
</script>
@endpush
