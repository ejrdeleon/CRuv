<div class="space-y-4">
    <!-- Header with Views & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900">Incident Ledger & Case Management</h1>
            <p class="text-xs text-slate-500">Filter, prioritize, verify, assign units, and update case statuses.</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-md p-0.5 bg-slate-200 border border-slate-300 text-xs">
                <button type="button" wire:click="$set('viewMode', 'table')"
                    class="px-2.5 py-1 rounded font-medium transition flex items-center gap-1.5 {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Table</span>
                </button>
                <button type="button" wire:click="$set('viewMode', 'map')"
                    class="px-2.5 py-1 rounded font-medium transition flex items-center gap-1.5 {{ $viewMode === 'map' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Map</span>
                </button>
            </div>

            <a href="{{ route('admin.analytics') }}" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export Ledger</span>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-lg p-3 border border-slate-200 shadow-xs space-y-2.5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 text-xs">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference #, title, resident name..."
                    class="w-full pl-8 pr-3 py-1.5 rounded-md border border-slate-300 text-xs focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
            </div>

            <!-- Status Filter -->
            <div>
                <select wire:model.live="statusFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 text-xs bg-white focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
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
                <select wire:model.live="priorityFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 text-xs bg-white focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <select wire:model.live="categoryFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 text-xs bg-white focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($search || $statusFilter || $priorityFilter || $categoryFilter || $departmentFilter)
            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100">
                <span class="text-slate-500 text-[11px]">Filters active</span>
                <button type="button" wire:click="resetFilters" class="text-teal-800 hover:underline font-semibold text-xs">Reset Filters</button>
            </div>
        @endif
    </div>

    @if($viewMode === 'map')
        <!-- Map View Mode -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs space-y-2" wire:ignore>
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">Geographic View: {{ count($mapReports) }} incidents mapped</span>
                <span class="text-slate-400 text-[11px]">Click marker to inspect dossier</span>
            </div>
            <div class="rounded-md border border-slate-300 overflow-hidden">
                <div id="management-filter-map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    @else
        <!-- Table View Mode -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
            @if ($reports->isEmpty())
                <div class="p-8 text-center text-xs">
                    <p class="font-semibold text-slate-800">No incident reports match criteria</p>
                    <p class="text-slate-500 mt-0.5">Try clearing filter conditions or searching a different term.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                            <tr>
                                <th class="py-2 px-3 cursor-pointer" wire:click="sortBy('reference_number')">
                                    <div class="flex items-center gap-1">
                                        <span>Reference #</span>
                                        @if($sortField === 'reference_number') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-2 px-3">Title & Location</th>
                                <th class="py-2 px-3">Category</th>
                                <th class="py-2 px-3 cursor-pointer" wire:click="sortBy('priority')">
                                    <div class="flex items-center gap-1">
                                        <span>Priority</span>
                                        @if($sortField === 'priority') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-2 px-3 cursor-pointer" wire:click="sortBy('status')">
                                    <div class="flex items-center gap-1">
                                        <span>Status</span>
                                        @if($sortField === 'status') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-2 px-3">Assignee / Unit</th>
                                <th class="py-2 px-3 cursor-pointer" wire:click="sortBy('created_at')">
                                    <div class="flex items-center gap-1">
                                        <span>Date</span>
                                        @if($sortField === 'created_at') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    </div>
                                </th>
                                <th class="py-2 px-3 text-right">Actions</th>
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
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-2.5 px-3 font-mono font-semibold text-slate-700 whitespace-nowrap text-[11px]">
                                        <a href="{{ route('admin.reports.show', $report->id) }}" class="text-teal-800 hover:underline">
                                            {{ $report->reference_number }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 px-3 max-w-[240px]">
                                        <p class="font-semibold text-slate-900 truncate">{{ $report->title }}</p>
                                        <p class="text-[11px] text-slate-500 truncate">{{ $report->address_text }}</p>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-700 max-w-[150px] truncate">
                                        {{ $report->category->name }}
                                    </td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] uppercase border {{ $pColors[$report->priority] ?? 'border-slate-200 text-slate-600' }}">
                                            {{ $report->priority }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium border {{ $sColors[$report->status] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $report->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 max-w-[140px] truncate">
                                        @if($report->assignedStaff)
                                            <span class="font-medium text-slate-900 block truncate">{{ $report->assignedStaff->name }}</span>
                                        @endif
                                        <span class="text-[11px] text-slate-400 block truncate">{{ $report->department?->name ?? 'Unassigned' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap text-[11px]">
                                        {{ $report->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.reports.show', $report->id) }}" class="px-2.5 py-1 rounded border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-slate-200">
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
            let color = '#0f766e';
            if (r.priority === 'urgent' || r.status === 'rejected') color = '#e11d48';
            else if (r.status === 'resolved') color = '#059669';
            else if (r.status === 'submitted' || r.status === 'under_review') color = '#d97706';
            else if (r.status === 'verified' || r.status === 'assigned') color = '#2563eb';

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:16px; height:16px; border-radius:50%; border:2px solid #ffffff; box-shadow:0 1px 3px rgba(0,0,0,0.35);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            L.marker([r.lat, r.lng], { icon: icon })
                .addTo(filterMap)
                .bindPopup(`
                    <div style="font-family:sans-serif; min-width:180px;">
                        <span style="font-family:monospace; font-size:10px; color:#64748b; font-weight:bold;">${r.ref}</span>
                        <h4 style="font-size:12px; font-weight:bold; margin:2px 0; color:#0f172a;">${r.title}</h4>
                        <div style="font-size:11px; color:#475569; margin-bottom:6px;">Status: <b>${r.status_label}</b></div>
                        <a href="${r.url}" style="display:inline-block; padding:3px 8px; background:#0f766e; color:#ffffff; font-size:11px; font-weight:bold; border-radius:4px; text-decoration:none;">Inspect Dossier</a>
                    </div>
                `);
        });
    }
</script>
@endpush
