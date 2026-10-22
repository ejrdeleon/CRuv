<div class="space-y-4">
    <!-- Header with Views & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Issue Queue & Case Management</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Filter, prioritize, verify, assign units, and update case statuses.</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-md p-0.5 bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs">
                <button type="button" wire:click="$set('viewMode', 'table')"
                    class="px-2.5 py-1 rounded font-medium transition flex items-center gap-1.5 {{ $viewMode === 'table' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Table</span>
                </button>
                <button type="button" wire:click="$set('viewMode', 'map')"
                    class="px-2.5 py-1 rounded font-medium transition flex items-center gap-1.5 {{ $viewMode === 'map' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Map</span>
                </button>
            </div>

            <a href="{{ route('admin.analytics') }}" class="px-3 py-1.5 rounded-md bg-teal-700 hover:bg-teal-600 text-white font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export Ledger</span>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-800 space-y-2.5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 text-xs">
            <div class="lg:col-span-2 relative">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference #, title, resident name..."
                    class="w-full pl-8 pr-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs focus:border-teal-600 focus:outline-none">
            </div>

            <div>
                <select wire:model.live="statusFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 text-xs bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none">
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

            <div>
                <select wire:model.live="priorityFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 text-xs bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent (Critical Hazard)</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <div>
                <select wire:model.live="categoryFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 text-xs bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($search || $statusFilter || $priorityFilter || $categoryFilter || $departmentFilter)
            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100 dark:border-slate-800">
                <span class="text-slate-500 dark:text-slate-400 text-[11px]">Filters active</span>
                <button type="button" wire:click="resetFilters" class="text-teal-600 dark:text-teal-400 hover:underline font-semibold text-xs">Reset Filters</button>
            </div>
        @endif
    </div>

    @if($viewMode === 'map')
        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800 space-y-2" wire:ignore>
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800 dark:text-slate-200">Geographic View: {{ count($mapReports) }} incidents mapped</span>
                <span class="text-slate-400 text-[11px]">Click marker to inspect dossier</span>
            </div>
            <div class="rounded-md border border-slate-300 dark:border-slate-800 overflow-hidden">
                <div id="management-filter-map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
            @if ($reports->isEmpty())
                <div class="p-8 text-center text-xs">
                    <p class="font-semibold text-slate-800 dark:text-slate-200">No incident reports match criteria</p>
                    <p class="text-slate-500 dark:text-slate-400 mt-0.5">Try clearing filter conditions or searching a different term.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
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
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($reports as $report)
                                @php
                                    $priorityToken = match($report->priority) {
                                        'urgent' => ['dot' => 'bg-[var(--color-status-hazard-dot)]', 'bg' => 'bg-[var(--color-status-hazard-bg-light)] dark:bg-[var(--color-status-hazard-bg-dark)]', 'text' => 'text-[var(--color-status-hazard-text-light)] dark:text-[var(--color-status-hazard-text-dark)]', 'label' => 'Critical Hazard'],
                                        'high' => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]', 'label' => 'High'],
                                        'medium' => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]', 'label' => 'Medium'],
                                        default => ['dot' => 'bg-[var(--color-status-low-dot)]', 'bg' => 'bg-[var(--color-status-low-bg-light)] dark:bg-[var(--color-status-low-bg-dark)]', 'text' => 'text-[var(--color-status-low-text-light)] dark:text-[var(--color-status-low-text-dark)]', 'label' => 'Low Priority'],
                                    };
                                    $statusToken = match($report->status) {
                                        'resolved' => ['dot' => 'bg-[var(--color-status-resolved-dot)]', 'bg' => 'bg-[var(--color-status-resolved-bg-light)] dark:bg-[var(--color-status-resolved-bg-dark)]', 'text' => 'text-[var(--color-status-resolved-text-light)] dark:text-[var(--color-status-resolved-text-dark)]'],
                                        'rejected', 'duplicate' => ['dot' => 'bg-[var(--color-status-hazard-dot)]', 'bg' => 'bg-[var(--color-status-hazard-bg-light)] dark:bg-[var(--color-status-hazard-bg-dark)]', 'text' => 'text-[var(--color-status-hazard-text-light)] dark:text-[var(--color-status-hazard-text-dark)]'],
                                        default => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]'],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60 transition">
                                    <td class="py-2.5 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-[11px] tabular-nums-metric">
                                        <a href="{{ route('admin.reports.show', $report->id) }}" class="text-teal-600 dark:text-teal-400 hover:underline">
                                            {{ $report->reference_number }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 px-3 max-w-[240px]">
                                        <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $report->title }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $report->address_text }}</p>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 max-w-[150px] truncate">
                                        {{ $report->category->name }}
                                    </td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold {{ $priorityToken['bg'] }} {{ $priorityToken['text'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $priorityToken['dot'] }}"></span>
                                            {{ $priorityToken['label'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold {{ $statusToken['bg'] }} {{ $statusToken['text'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusToken['dot'] }}"></span>
                                            {{ $report->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 max-w-[140px] truncate">
                                        @if($report->assignedStaff)
                                            <span class="font-medium text-slate-900 dark:text-white block truncate">{{ $report->assignedStaff->name }}</span>
                                        @endif
                                        <span class="text-[11px] text-slate-400 block truncate">{{ $report->department?->name ?? 'Unassigned' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 whitespace-nowrap text-[11px] tabular-nums-metric">
                                        {{ $report->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.reports.show', $report->id) }}" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-xs transition">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-slate-200 dark:border-slate-800">
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
        }).setView([14.2816, 121.4146], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(filterMap);

        reports.forEach(r => {
            let color = '#d97706';
            if (r.priority === 'urgent' || r.status === 'rejected') color = '#e11d48';
            else if (r.status === 'resolved') color = '#059669';
            else if (r.priority === 'low' && r.status === 'submitted') color = '#db2777';

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:14px; height:14px; border-radius:50%; border:2px solid #ffffff;"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            L.marker([r.lat, r.lng], { icon: icon })
                .addTo(filterMap)
                .bindPopup(`
                    <div style="font-family:Inter,sans-serif; min-width:180px;">
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
