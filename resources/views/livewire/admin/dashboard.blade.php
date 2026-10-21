<div class="space-y-6">
    <!-- Top Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Total -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Total Reports</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-slate-900">{{ $totalReports }}</span>
                <span class="p-1.5 rounded-lg bg-slate-100 text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
        </div>

        <!-- New / Submitted -->
        <div class="bg-white rounded-2xl p-4 border border-blue-200 bg-blue-50/20 shadow-sm">
            <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">New Submissions</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-blue-700">{{ $newReports }}</span>
                <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </span>
            </div>
        </div>

        <!-- Awaiting Verification -->
        <div class="bg-white rounded-2xl p-4 border border-amber-200 bg-amber-50/20 shadow-sm">
            <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">Needs Verification</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-amber-700">{{ $awaitingVerification }}</span>
                <span class="p-1.5 rounded-lg bg-amber-100 text-amber-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </span>
            </div>
        </div>

        <!-- Urgent / High Priority -->
        <div class="bg-white rounded-2xl p-4 border border-rose-200 bg-rose-50/20 shadow-sm">
            <span class="text-xs font-bold text-rose-800 uppercase tracking-wide">Urgent / Hazards</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-rose-700">{{ $urgentReports }}</span>
                <span class="p-1.5 rounded-lg bg-rose-100 text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white rounded-2xl p-4 border border-teal-200 bg-teal-50/20 shadow-sm">
            <span class="text-xs font-bold text-teal-800 uppercase tracking-wide">In Progress</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-teal-700">{{ $inProgressReports }}</span>
                <span class="p-1.5 rounded-lg bg-teal-100 text-teal-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white rounded-2xl p-4 border border-emerald-200 bg-emerald-50/20 shadow-sm">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Resolved</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-700">{{ $resolvedReports }}</span>
                <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
        </div>
    </div>

    <!-- Community Map & Category Distribution Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Leaflet Map Card -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Live Incident Map</h2>
                    <p class="text-xs text-slate-500">Real-time coordinates of reported damage across Barangay San Isidro.</p>
                </div>
                <a href="{{ route('admin.map') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1">
                    <span>Full Screen Map</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <!-- Map container -->
            <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-inner relative" wire:ignore>
                <div id="admin-dashboard-map" style="height: 340px; width: 100%;"></div>
            </div>

            <!-- Map legend -->
            <div class="flex items-center justify-between text-[11px] text-slate-600 px-1 pt-1 flex-wrap gap-2">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Urgent Hazard
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Under Review / Submitted
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> In Progress
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Resolved
                    </span>
                </div>
                <span class="font-medium text-slate-400">Click marker to inspect</span>
            </div>
        </div>

        <!-- Category Breakdown Card -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Incident by Category</h2>
                <p class="text-xs text-slate-500 mb-3">Breakdown of reported hazards.</p>

                <div class="space-y-3">
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
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full {{ $cat->is_hazardous ? 'bg-rose-500' : 'bg-teal-600' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Priority Breakdown Bar -->
            <div class="pt-4 border-t border-slate-100">
                <span class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Priority Distribution</span>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="p-2 bg-slate-50 rounded-xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-semibold">Low</span>
                        <span class="font-bold text-slate-700">{{ $priorityCounts['low'] ?? 0 }}</span>
                    </div>
                    <div class="p-2 bg-blue-50/50 rounded-xl">
                        <span class="block text-[10px] text-blue-500 uppercase font-semibold">Medium</span>
                        <span class="font-bold text-blue-700">{{ $priorityCounts['medium'] ?? 0 }}</span>
                    </div>
                    <div class="p-2 bg-amber-50/50 rounded-xl">
                        <span class="block text-[10px] text-amber-500 uppercase font-semibold">High</span>
                        <span class="font-bold text-amber-700">{{ $priorityCounts['high'] ?? 0 }}</span>
                    </div>
                    <div class="p-2 bg-rose-50/50 rounded-xl">
                        <span class="block text-[10px] text-rose-500 uppercase font-semibold">Urgent</span>
                        <span class="font-bold text-rose-700">{{ $priorityCounts['urgent'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports Table & Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Reports Table -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Recent Community Reports</h2>
                    <p class="text-xs text-slate-500">Latest submissions requiring action or verification.</p>
                </div>
                <a href="{{ route('admin.reports') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                    View all reports →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-semibold uppercase">
                            <th class="pb-2.5">Ref #</th>
                            <th class="pb-2.5">Title & Category</th>
                            <th class="pb-2.5">Priority</th>
                            <th class="pb-2.5">Status</th>
                            <th class="pb-2.5">Assigned</th>
                            <th class="pb-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentReports as $rep)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 font-mono font-bold text-slate-600">
                                    {{ $rep->reference_number }}
                                </td>
                                <td class="py-3 max-w-[220px]">
                                    <p class="font-bold text-slate-900 truncate">{{ $rep->title }}</p>
                                    <span class="text-[11px] text-slate-500 truncate block">{{ $rep->category->name }}</span>
                                </td>
                                <td class="py-3">
                                    @php
                                        $pColors = [
                                            'urgent' => 'text-rose-700 font-bold bg-rose-50 border-rose-200',
                                            'high' => 'text-amber-800 font-semibold bg-amber-50 border-amber-200',
                                            'medium' => 'text-blue-800 bg-blue-50 border-blue-200',
                                            'low' => 'text-slate-600 bg-slate-100 border-slate-200',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] uppercase border {{ $pColors[$rep->priority] ?? '' }}">
                                        {{ $rep->priority }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    @php
                                        $sColors = [
                                            'submitted' => 'bg-slate-100 text-slate-700',
                                            'under_review' => 'bg-amber-100 text-amber-800',
                                            'verified' => 'bg-blue-100 text-blue-800',
                                            'assigned' => 'bg-indigo-100 text-indigo-800',
                                            'in_progress' => 'bg-teal-100 text-teal-800',
                                            'resolved' => 'bg-emerald-100 text-emerald-800',
                                            'rejected' => 'bg-rose-100 text-rose-800',
                                            'duplicate' => 'bg-slate-100 text-slate-600',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $sColors[$rep->status] ?? '' }}">
                                        {{ $rep->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-600 truncate max-w-[120px]">
                                    {{ $rep->assignedStaff ? $rep->assignedStaff->name : 'Unassigned' }}
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.reports.show', $rep->id) }}" class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 font-semibold text-xs transition">
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
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900">System Activity Audit</h2>
            <div class="space-y-3">
                @foreach($recentActivities as $act)
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1 text-xs">
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
            let color = '#0d9488'; // teal default
            if (r.priority === 'urgent' || r.status === 'rejected') color = '#e11d48'; // rose
            else if (r.status === 'resolved') color = '#10b981'; // emerald
            else if (r.status === 'submitted' || r.status === 'under_review') color = '#f59e0b'; // amber
            else if (r.status === 'verified' || r.status === 'assigned') color = '#3b82f6'; // blue

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:18px; height:18px; border-radius:50%; border:2px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9]
            });

            const popupContent = `
                <div style="font-family:inherit; min-width:180px;">
                    <div style="font-size:10px; font-weight:bold; color:#64748b; font-family:monospace;">${r.ref}</div>
                    <div style="font-size:12px; font-weight:bold; color:#0f172a; margin-top:2px;">${r.title}</div>
                    <div style="font-size:11px; color:#475569; margin:4px 0;">${r.category}</div>
                    <div style="margin-top:6px;">
                        <a href="${r.url}" style="display:inline-block; padding:3px 8px; background:#0d9488; color:#ffffff; font-size:11px; font-weight:bold; border-radius:6px; text-decoration:none;">View & Manage</a>
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
