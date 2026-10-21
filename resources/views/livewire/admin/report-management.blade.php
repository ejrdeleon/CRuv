<div class="space-y-4">
    <!-- Header with Views & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Community Reports Management</h1>
            <p class="text-xs text-slate-500">Monitor, prioritize, assign, and update hazard cases.</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-xl p-1 bg-slate-200/80 border border-slate-300">
                <button type="button" wire:click="$set('viewMode', 'table')"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Table</span>
                </button>
                <button type="button" wire:click="$set('viewMode', 'map')"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $viewMode === 'map' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Map View</span>
                </button>
            </div>

            <a href="{{ route('admin.analytics') }}" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference #, title, resident name..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
            </div>

            <!-- Status Filter -->
            <div>
                <select wire:model.live="statusFilter" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-white focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    <option value="">All Statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="under_review">Under Review</option>
                    <option value="verified">Verified</option>
                    <option value="assigned">Assigned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="rejected">Rejected</option>
                    <option value="duplicate">Duplicate</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <select wire:model.live="priorityFilter" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-white focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <select wire:model.live="categoryFilter" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-white focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($search || $statusFilter || $priorityFilter || $categoryFilter || $departmentFilter)
            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                <span class="text-slate-500">Active filters applied</span>
                <button type="button" wire:click="resetFilters" class="text-teal-700 hover:text-teal-800 font-bold">Clear All Filters</button>
            </div>
        @endif
    </div>

    @if($viewMode === 'map')
        <!-- Map View Mode -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3" wire:ignore>
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">Showing {{ count($mapReports) }} filtered locations on map</span>
                <span class="text-slate-400">Click a marker to view details</span>
            </div>
            <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-inner">
                <div id="management-filter-map" style="height: 520px; width: 100%;"></div>
            </div>
        </div>
    @else
        <!-- Table View Mode -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if ($reports->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No community reports match your query</h3>
                    <p class="text-xs text-slate-500 mt-1">Try clearing some search terms or filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 cursor-pointer" wire:click="sortBy('reference_number')">
                                    <div class="flex items-center gap-1">
                                        <span>Reference #</span>
                                        @if($sortField === 'reference_number') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-3 px-4">Title & Location</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4 cursor-pointer" wire:click="sortBy('priority')">
                                    <div class="flex items-center gap-1">
                                        <span>Priority</span>
                                        @if($sortField === 'priority') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-3 px-4 cursor-pointer" wire:click="sortBy('status')">
                                    <div class="flex items-center gap-1">
                                        <span>Status</span>
                                        @if($sortField === 'status') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-3 px-4">Assignee / Dept</th>
                                <th class="py-3 px-4 cursor-pointer" wire:click="sortBy('created_at')">
                                    <div class="flex items-center gap-1">
                                        <span>Date</span>
                                        @if($sortField === 'created_at') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($reports as $report)
                                @php
                                    $pColors = [
                                        'urgent' => 'text-rose-700 font-bold bg-rose-50 border-rose-200',
                                        'high' => 'text-amber-800 font-semibold bg-amber-50 border-amber-200',
                                        'medium' => 'text-blue-800 bg-blue-50 border-blue-200',
                                        'low' => 'text-slate-600 bg-slate-100 border-slate-200',
                                    ];
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
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-700 whitespace-nowrap">
                                        <a href="{{ route('admin.reports.show', $report->id) }}" class="text-teal-700 hover:text-teal-800 hover:underline">
                                            {{ $report->reference_number }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 max-w-[260px]">
                                        <p class="font-bold text-slate-900 truncate">{{ $report->title }}</p>
                                        <p class="text-[11px] text-slate-500 truncate">{{ $report->address_text }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 max-w-[160px] truncate">
                                        {{ $report->category->name }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] uppercase border {{ $pColors[$report->priority] ?? '' }}">
                                            {{ $report->priority }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $sColors[$report->status] ?? '' }}">
                                            {{ $report->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 max-w-[150px] truncate">
                                        @if($report->assignedStaff)
                                            <span class="font-medium text-slate-900 block truncate">{{ $report->assignedStaff->name }}</span>
                                        @endif
                                        <span class="text-[11px] text-slate-400 block truncate">{{ $report->department?->name ?? 'Unassigned' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                        {{ $report->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.reports.show', $report->id) }}" class="px-3 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 font-semibold text-xs transition">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
    let filterMap = null;

    document.addEventListener('DOMContentLoaded', function() {
        initFilterMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initFilterMap();
    });

    function initFilterMap() {
        const container = document.getElementById('management-filter-map');
        if (!container || filterMap) return;

        const reports = @json($mapReports);

        filterMap = L.map('management-filter-map', {
            zoomControl: true
        }).setView([14.1815, 121.2845], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(filterMap);

        reports.forEach(r => {
            let color = '#0d9488';
            if (r.priority === 'urgent' || r.status === 'rejected') color = '#e11d48';
            else if (r.status === 'resolved') color = '#10b981';
            else if (r.status === 'submitted' || r.status === 'under_review') color = '#f59e0b';
            else if (r.status === 'verified' || r.status === 'assigned') color = '#3b82f6';

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:20px; height:20px; border-radius:50%; border:2px solid #ffffff; box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            L.marker([r.lat, r.lng], { icon: icon })
                .addTo(filterMap)
                .bindPopup(`
                    <div style="font-family:sans-serif; min-width:180px;">
                        <span style="font-family:monospace; font-size:10px; color:#64748b; font-weight:bold;">${r.ref}</span>
                        <h4 style="font-size:12px; font-weight:bold; margin:2px 0; color:#0f172a;">${r.title}</h4>
                        <div style="font-size:11px; color:#475569; margin-bottom:6px;">Status: <b>${r.status_label}</b></div>
                        <a href="${r.url}" style="display:inline-block; padding:4px 8px; background:#0d9488; color:#ffffff; font-size:11px; font-weight:bold; border-radius:6px; text-decoration:none;">Inspect Report</a>
                    </div>
                `);
        });
    }
</script>
@endpush
