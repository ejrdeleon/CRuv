<div
    class="space-y-4 relative"
    x-data="{
        activeRowIndex: 0,
        rowIds: @js($recentReports->pluck('id')->values()),
        contextMenu: { open: false, x: 0, y: 0, reportId: null, ref: '', url: '' },
        openContextMenu(event, reportId, ref, url) {
            event.preventDefault();
            this.contextMenu = {
                open: true,
                x: Math.min(event.clientX, window.innerWidth - 200),
                y: Math.min(event.clientY, window.innerHeight - 160),
                reportId,
                ref,
                url
            };
        },
        handleKeydown(e) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
            if (e.key === 'j' || e.key === 'J') {
                e.preventDefault();
                if (this.rowIds.length > 0) {
                    this.activeRowIndex = Math.min(this.activeRowIndex + 1, this.rowIds.length - 1);
                    $wire.selectReport(this.rowIds[this.activeRowIndex]);
                }
            } else if (e.key === 'k' || e.key === 'K') {
                e.preventDefault();
                if (this.rowIds.length > 0) {
                    this.activeRowIndex = Math.max(this.activeRowIndex - 1, 0);
                    $wire.selectReport(this.rowIds[this.activeRowIndex]);
                }
            } else if (e.key === 'Enter' && this.rowIds[this.activeRowIndex]) {
                $wire.selectReport(this.rowIds[this.activeRowIndex]);
            } else if ((e.key === 's' || e.key === 'S') && document.getElementById('drawer-status-select')) {
                e.preventDefault();
                document.getElementById('drawer-status-select').focus();
            } else if ((e.key === 'a' || e.key === 'A') && document.getElementById('drawer-assign-select')) {
                e.preventDefault();
                document.getElementById('drawer-assign-select').focus();
            } else if (e.key === 'Escape') {
                this.contextMenu.open = false;
                $wire.closeDrawer();
            }
        }
    }"
    @keydown.window="handleKeydown($event)"
    @click.window="contextMenu.open = false"
>
    <!-- Top Compact Stat Row: Real Operational Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- Total Active Tickets -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Active Tickets</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums-metric">{{ $totalActiveTickets }}</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $newReports }} new • {{ $inProgressReports }} in progress</span>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[11px] font-semibold bg-[var(--color-status-progress-bg-light)] text-[var(--color-status-progress-text-light)] dark:bg-[var(--color-status-progress-bg-dark)] dark:text-[var(--color-status-progress-text-dark)]">
                <span class="w-2 h-2 rounded-full bg-[var(--color-status-progress-dot)]"></span>
                Active Queue
            </span>
        </div>

        <!-- Average Resolution Time -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Average Resolution Time</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums-metric">
                        {{ $avgResolutionHours !== null ? $avgResolutionHours . ' hrs' : 'N/A' }}
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $resolvedReports }} resolved cases</span>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[11px] font-semibold bg-[var(--color-status-resolved-bg-light)] text-[var(--color-status-resolved-text-light)] dark:bg-[var(--color-status-resolved-bg-dark)] dark:text-[var(--color-status-resolved-text-dark)]">
                <span class="w-2 h-2 rounded-full bg-[var(--color-status-resolved-dot)]"></span>
                Resolved
            </span>
        </div>

        <!-- High Priority Unassigned -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">High Priority Unassigned</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 tabular-nums-metric">{{ $highPriorityUnassigned }}</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $urgentReports }} total critical/high</span>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[11px] font-semibold bg-[var(--color-status-hazard-bg-light)] text-[var(--color-status-hazard-text-light)] dark:bg-[var(--color-status-hazard-bg-dark)] dark:text-[var(--color-status-hazard-text-dark)]">
                <span class="w-2 h-2 rounded-full bg-[var(--color-status-hazard-dot)]"></span>
                Critical Hazard
            </span>
        </div>
    </div>

    <!-- Main Workspace: Shared Spatial Map & Dense Data Table Hybrid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- Left Column (5/12): Shared Interactive Spatial Map Component -->
        <div class="lg:col-span-5">
            <x-admin.spatial-map
                map-id="admin-dashboard-map"
                :markers="$mapReports"
                height="460px"
                title="Live Spatial Map"
                subtitle="Click any table row to pan map & open drawer, or click a pin."
                :show-expand-link="true"
            />
        </div>

        <!-- Right Column (7/12): Dense Triage Data Table -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Triage Queue</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Click row to pan map & inspect. Right-click for quick actions.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline-block text-[11px] text-slate-400 font-mono">J/K: Move • S: Status • A: Assign</span>
                    <a href="{{ route('admin.reports') }}" class="px-2.5 py-1 rounded border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Full Queue
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[505px]">
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold text-[11px]">
                        <tr>
                            <th class="py-2 px-3">Ref #</th>
                            <th class="py-2 px-3">Issue & Category</th>
                            <th class="py-2 px-3">Priority</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Assigned</th>
                            <th class="py-2 px-3 text-right">Submitted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($recentReports as $idx => $rep)
                            @php
                                $isSelected = $selectedReportId === $rep->id;
                                $priorityToken = match($rep->priority) {
                                    'urgent' => ['dot' => 'bg-[var(--color-status-hazard-dot)]', 'bg' => 'bg-[var(--color-status-hazard-bg-light)] dark:bg-[var(--color-status-hazard-bg-dark)]', 'text' => 'text-[var(--color-status-hazard-text-light)] dark:text-[var(--color-status-hazard-text-dark)]', 'label' => 'Critical Hazard'],
                                    'high' => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]', 'label' => 'High'],
                                    'medium' => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]', 'label' => 'Medium'],
                                    default => ['dot' => 'bg-[var(--color-status-low-dot)]', 'bg' => 'bg-[var(--color-status-low-bg-light)] dark:bg-[var(--color-status-low-bg-dark)]', 'text' => 'text-[var(--color-status-low-text-light)] dark:text-[var(--color-status-low-text-dark)]', 'label' => 'Low Priority'],
                                };

                                $statusToken = match($rep->status) {
                                    'resolved' => ['dot' => 'bg-[var(--color-status-resolved-dot)]', 'bg' => 'bg-[var(--color-status-resolved-bg-light)] dark:bg-[var(--color-status-resolved-bg-dark)]', 'text' => 'text-[var(--color-status-resolved-text-light)] dark:text-[var(--color-status-resolved-text-dark)]'],
                                    'rejected', 'duplicate' => ['dot' => 'bg-[var(--color-status-hazard-dot)]', 'bg' => 'bg-[var(--color-status-hazard-bg-light)] dark:bg-[var(--color-status-hazard-bg-dark)]', 'text' => 'text-[var(--color-status-hazard-text-light)] dark:text-[var(--color-status-hazard-text-dark)]'],
                                    default => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]'],
                                };
                            @endphp
                            <tr
                                wire:key="triage-row-{{ $rep->id }}"
                                data-report-row-id="{{ $rep->id }}"
                                wire:click="selectReport({{ $rep->id }})"
                                @click="activeRowIndex = {{ $idx }}"
                                @contextmenu="openContextMenu($event, {{ $rep->id }}, '{{ $rep->reference_number }}', '{{ route('admin.reports.show', $rep->id) }}')"
                                class="cursor-pointer transition {{ $isSelected ? 'bg-teal-50/90 dark:bg-teal-950/60 ring-1 ring-inset ring-teal-600/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                            >
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300 text-[11px] whitespace-nowrap tabular-nums-metric">
                                    {{ $rep->reference_number }}
                                </td>
                                <td class="py-2.5 px-3 max-w-[210px]">
                                    <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $rep->title }}</p>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate block">{{ $rep->category->name ?? 'General' }} • {{ $rep->address_text }}</span>
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
                                        {{ $rep->status_label }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 truncate max-w-[120px]">
                                    {{ $rep->assignedStaff ? $rep->assignedStaff->name : ($rep->department ? $rep->department->code : 'Unassigned') }}
                                </td>
                                <td class="py-2.5 px-3 text-right text-slate-500 dark:text-slate-400 whitespace-nowrap text-[11px] tabular-nums-metric">
                                    {{ $rep->created_at->format('M d') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right-Click Row Context Menu -->
    <div
        x-show="contextMenu.open"
        style="display: none;"
        :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px;`"
        class="fixed z-50 w-48 rounded-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-lg py-1 text-xs"
    >
        <div class="px-3 py-1.5 border-b border-slate-100 dark:border-slate-800 font-mono text-[10px] text-slate-400" x-text="contextMenu.ref"></div>
        <button
            type="button"
            @click="$wire.selectReport(contextMenu.reportId); contextMenu.open = false;"
            class="w-full text-left px-3 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-between"
        >
            <span>Open Triage Drawer</span>
            <span class="font-mono text-[10px] text-slate-400">Enter</span>
        </button>
        <a
            :href="contextMenu.url"
            class="block px-3 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200"
        >
            Open Full Case Dossier →
        </a>
    </div>

    <!-- Shared Triage Detail Drawer -->
    <x-admin.triage-drawer
        :selected-report="$selectedReport"
        :departments="$departments"
        :staff-members="$staffMembers"
    />
</div>
