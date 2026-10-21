<div class="space-y-4">
    <!-- Top Filter Controls -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900">Interactive Community Incident Map</h1>
                <p class="text-xs text-slate-500">Visual geospatial monitoring of hazards and ongoing infrastructure work.</p>
            </div>

            <!-- Legend pills -->
            <div class="flex items-center gap-3 text-xs flex-wrap">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-600 shadow-sm"></span> Urgent</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500 shadow-sm"></span> Review</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 shadow-sm"></span> Verified</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-teal-600 shadow-sm"></span> In Progress</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-600 shadow-sm"></span> Resolved</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 pt-2 border-t border-slate-100 text-xs">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference or title..."
                class="rounded-xl border border-slate-200 px-3 py-2">

            <select wire:model.live="categoryFilter" class="rounded-xl border border-slate-200 px-3 py-2 bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="rounded-xl border border-slate-200 px-3 py-2 bg-white">
                <option value="">All Statuses</option>
                <option value="submitted">Submitted</option>
                <option value="under_review">Under Review</option>
                <option value="verified">Verified</option>
                <option value="assigned">Assigned</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>

            <select wire:model.live="priorityFilter" class="rounded-xl border border-slate-200 px-3 py-2 bg-white">
                <option value="">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>

            <button type="button" wire:click="resetFilters" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-center">
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Map & Side Details Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Interactive Map Container -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-4 border border-slate-200 shadow-sm space-y-2" wire:ignore>
            <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-inner relative">
                <div id="full-interactive-map" style="height: 600px; width: 100%;"></div>
            </div>
        </div>

        <!-- Sidebar List / Selected Report Detail -->
        <div class="space-y-4">
            @if($selectedReport)
                <div class="bg-white rounded-3xl p-5 border border-teal-300 shadow-md space-y-4 animate-in fade-in">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-xs font-mono font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                            {{ $selectedReport->reference_number }}
                        </span>
                        <button type="button" wire:click="clearSelectedReport" class="text-slate-400 hover:text-slate-700 text-sm font-bold">×</button>
                    </div>

                    @if($selectedReport->images->first())
                        <div class="aspect-video rounded-2xl overflow-hidden border border-slate-200 bg-slate-100">
                            <img src="{{ $selectedReport->images->first()->url }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900 leading-snug">{{ $selectedReport->title }}</h3>
                        <p class="text-xs text-slate-500">{{ $selectedReport->category->name }}</p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1 text-slate-700">
                        <p class="font-medium truncate">{{ $selectedReport->address_text }}</p>
                        <p class="text-slate-400 font-mono text-[11px]">{{ $selectedReport->latitude }}, {{ $selectedReport->longitude }}</p>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="px-2.5 py-1 rounded-full font-bold bg-teal-50 text-teal-800 border border-teal-200">
                            {{ $selectedReport->status_label }}
                        </span>
                        <span class="font-bold uppercase text-[11px] {{ $selectedReport->priority === 'urgent' ? 'text-rose-600' : 'text-slate-500' }}">
                            {{ $selectedReport->priority }} Priority
                        </span>
                    </div>

                    <a href="{{ route('admin.reports.show', $selectedReport->id) }}"
                        class="block w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs text-center shadow-sm transition">
                        Open Report Management & Actions →
                    </a>
                </div>
            @endif

            <!-- List of Pins in this View -->
            <div class="bg-white rounded-3xl p-4 border border-slate-200 shadow-sm space-y-3 max-h-[580px] overflow-y-auto">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Filtered Pins ({{ $reports->count() }})</h3>
                    <span class="text-[11px] text-slate-400">Tap to inspect</span>
                </div>

                @if($reports->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-6">No matching pins found on map.</p>
                @else
                    <div class="space-y-2">
                        @foreach($reports as $r)
                            <div wire:click="selectReport({{ $r->id }})"
                                class="p-3 rounded-2xl border text-xs cursor-pointer transition shadow-sm {{ $selectedReportId === $r->id ? 'bg-teal-50/60 border-teal-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-mono text-[10px] font-bold text-slate-500">{{ $r->reference_number }}</span>
                                    <span class="text-[10px] uppercase font-bold {{ $r->priority === 'urgent' ? 'text-rose-600' : 'text-slate-500' }}">
                                        {{ $r->priority }}
                                    </span>
                                </div>
                                <p class="font-bold text-slate-900 truncate">{{ $r->title }}</p>
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
            let color = '#0d9488';
            if (m.priority === 'urgent') color = '#e11d48';
            else if (m.status === 'resolved') color = '#10b981';
            else if (m.status === 'submitted' || m.status === 'under_review') color = '#f59e0b';
            else if (m.status === 'verified' || m.status === 'assigned') color = '#3b82f6';

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:22px; height:22px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11]
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
                    <a href="${m.url}" style="display:inline-block; margin-top:4px; padding:3px 8px; background:#0d9488; color:#ffffff; font-size:11px; font-weight:bold; border-radius:6px; text-decoration:none;">Open Report</a>
                </div>
            `);
        });
    }
</script>
@endpush
