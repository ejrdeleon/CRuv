<div class="space-y-4">
    <!-- Top Stat Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
        <!-- Total -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total Incidents</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-bold text-slate-900">{{ $totalReports }}</span>
                <span class="text-[10px] text-slate-400 font-medium">All logged</span>
            </div>
        </div>

        <!-- New / Submitted -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wide">New Submissions</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-bold text-slate-900">{{ $newReports }}</span>
                <span class="text-[10px] text-amber-700 font-semibold">Unreviewed</span>
            </div>
        </div>

        <!-- Awaiting Verification -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wide">Under Review</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-bold text-amber-800">{{ $awaitingVerification }}</span>
                <span class="text-[10px] text-slate-500">Inspection</span>
            </div>
        </div>

        <!-- Urgent / Hazards -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-rose-800 uppercase tracking-wide">Critical Hazards</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-bold text-rose-700">{{ $urgentReports }}</span>
                <span class="text-[10px] text-rose-600 font-semibold">Immediate</span>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-teal-800 uppercase tracking-wide">In Progress</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-bold text-teal-800">{{ $inProgressReports }}</span>
                <span class="text-[10px] text-slate-500">Field work</span>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wide">Resolved</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-bold text-emerald-700">{{ $resolvedReports }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold">Closed</span>
            </div>
        </div>
    </div>

    <!-- Community Map & Category Distribution Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Leaflet Map Card -->
        <div class="lg:col-span-2 bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Incident Spatial Distribution</h2>
                    <p class="text-[11px] text-slate-500">Geographic pinpoints across Barangay San Isidro.</p>
                </div>
                <a href="{{ route('admin.map') }}" class="text-xs font-semibold text-teal-800 hover:underline flex items-center gap-1">
                    <span>Expanded Map</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <!-- Map container -->
            <div class="rounded-md border border-slate-300 overflow-hidden relative" wire:ignore>
                <div id="admin-dashboard-map" style="height: 320px; width: 100%;"></div>
            </div>

            <!-- Map legend -->
            <div class="flex items-center justify-between text-[11px] text-slate-600 pt-0.5 flex-wrap gap-2">
                <div class="flex items-center gap-3.5 flex-wrap">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Urgent Hazard
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Unverified / New
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-700"></span> In Progress
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Resolved
                    </span>
                </div>
                <span class="text-slate-400 text-[10px]">Click any pin to inspect dossier</span>
            </div>
        </div>

        <!-- Category Breakdown Card -->
        <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-3 flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Incidents by Category</h2>
                <p class="text-[11px] text-slate-500 mb-3">Volume distribution across hazard types.</p>

                <div class="space-y-2.5">
                    @php $maxCatCount = max(1, $categoryBreakdown->max('reports_count')); @endphp
                    @foreach($categoryBreakdown as $cat)
                        @php
                            $pct = round(($cat->reports_count / $maxCatCount) * 100);
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-700 truncate max-w-[180px]">{{ $cat->name }}</span>
                                <span class="font-bold text-slate-900">{{ $cat->reports_count }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded {{ $cat->is_hazardous ? 'bg-rose-600' : 'bg-teal-800' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Priority Breakdown Bar -->
            <div class="pt-3 border-t border-slate-100">
                <span class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">Priority Ledger</span>
                <div class="grid grid-cols-4 gap-1.5 text-center text-xs">
                    <div class="p-1.5 bg-slate-50 rounded border border-slate-200">
                        <span class="block text-[10px] text-slate-500 font-semibold uppercase">Low</span>
                        <span class="font-bold text-slate-700">{{ $priorityCounts['low'] ?? 0 }}</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 rounded border border-slate-200">
                        <span class="block text-[10px] text-blue-700 font-semibold uppercase">Med</span>
                        <span class="font-bold text-blue-800">{{ $priorityCounts['medium'] ?? 0 }}</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 rounded border border-slate-200">
                        <span class="block text-[10px] text-amber-700 font-semibold uppercase">High</span>
                        <span class="font-bold text-amber-800">{{ $priorityCounts['high'] ?? 0 }}</span>
                    </div>
                    <div class="p-1.5 bg-slate-50 rounded border border-slate-200">
                        <span class="block text-[10px] text-rose-700 font-semibold uppercase">Urg</span>
                        <span class="font-bold text-rose-800">{{ $priorityCounts['urgent'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports Table & Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Reports Table -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-xs space-y-0 overflow-hidden">
            <div class="p-3.5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Recent Incidents</h2>
                    <p class="text-[11px] text-slate-500">Awaiting review, assignment, or completion.</p>
                </div>
                <a href="{{ route('admin.reports') }}" class="text-xs font-semibold text-teal-800 hover:underline">
                    View Ledger →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                            <th class="py-2 px-3">Ref #</th>
                            <th class="py-2 px-3">Title & Category</th>
                            <th class="py-2 px-3">Priority</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Assigned</th>
                            <th class="py-2 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentReports as $rep)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-600 text-[11px]">
                                    {{ $rep->reference_number }}
                                </td>
                                <td class="py-2.5 px-3 max-w-[200px]">
                                    <p class="font-semibold text-slate-900 truncate">{{ $rep->title }}</p>
                                    <span class="text-[11px] text-slate-500 truncate block">{{ $rep->category->name }}</span>
                                </td>
                                <td class="py-2.5 px-3">
                                    @php
                                        $pColors = [
                                            'urgent' => 'text-rose-700 font-bold bg-rose-50 border-rose-200',
                                            'high' => 'text-amber-800 font-semibold bg-amber-50 border-amber-200',
                                            'medium' => 'text-blue-800 bg-blue-50 border-blue-200',
                                            'low' => 'text-slate-600 bg-slate-100 border-slate-200',
                                        ];
                                    @endphp
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[10px] uppercase border {{ $pColors[$rep->priority] ?? 'border-slate-200 text-slate-600' }}">
                                        {{ $rep->priority }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3">
                                    @php
                                        $sColors = [
                                            'submitted' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'under_review' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'verified' => 'bg-blue-50 text-blue-800 border-blue-200',
                                            'assigned' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                            'in_progress' => 'bg-teal-50 text-teal-800 border-teal-200',
                                            'resolved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                                            'duplicate' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium border {{ $sColors[$rep->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $rep->status_label }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-600 truncate max-w-[120px]">
                                    {{ $rep->assignedStaff ? $rep->assignedStaff->name : 'Unassigned' }}
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <a href="{{ route('admin.reports.show', $rep->id) }}" class="px-2.5 py-1 rounded border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-3">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">System Audit Trail</h2>
            <div class="divide-y divide-slate-100 text-xs">
                @foreach($recentActivities as $act)
                    <div class="py-2.5 first:pt-0 last:pb-0 space-y-0.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-900">{{ $act->user ? $act->user->name : 'System' }}</span>
                            <span class="text-slate-400">{{ $act->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-600 leading-snug">{{ $act->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initAdminMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initAdminMap();
    });

    function initAdminMap() {
        const container = document.getElementById('admin-dashboard-map');
        if (!container) return;

        const reports = @json($mapReports);

        const map = L.map('admin-dashboard-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([14.1815, 121.2845], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        reports.forEach(r => {
            let color = '#0f766e'; // teal default
            if (r.priority === 'urgent' || r.status === 'rejected') color = '#e11d48'; // rose
            else if (r.status === 'resolved') color = '#059669'; // emerald
            else if (r.status === 'submitted' || r.status === 'under_review') color = '#d97706'; // amber
            else if (r.status === 'verified' || r.status === 'assigned') color = '#2563eb'; // blue

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:16px; height:16px; border-radius:50%; border:2px solid #ffffff; box-shadow:0 1px 3px rgba(0,0,0,0.35);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            const popupContent = `
                <div style="font-family:inherit; min-width:180px;">
                    <div style="font-size:10px; font-weight:bold; color:#64748b; font-family:monospace;">${r.ref}</div>
                    <div style="font-size:12px; font-weight:bold; color:#0f172a; margin-top:2px;">${r.title}</div>
                    <div style="font-size:11px; color:#475569; margin:4px 0;">${r.category}</div>
                    <div style="margin-top:6px;">
                        <a href="${r.url}" style="display:inline-block; padding:3px 8px; background:#0f766e; color:#ffffff; font-size:11px; font-weight:bold; border-radius:4px; text-decoration:none;">Inspect Dossier</a>
                    </div>
                </div>
            `;

            L.marker([r.lat, r.lng], { icon: icon })
                .addTo(map)
                .bindPopup(popupContent);
        });
    }
</script>
@endpush
