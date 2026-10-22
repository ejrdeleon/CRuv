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

    <!-- Main Workspace: Map & Dense Data Table Hybrid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- Left Column (5/12): Interactive Spatial Map -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 p-3.5 space-y-2.5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Live Spatial Map</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Click any row on the right to pan map & open triage drawer.</p>
                </div>
                <a href="{{ route('admin.map') }}" class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                    Expand Map →
                </a>
            </div>

            <div class="rounded-md border border-slate-200 dark:border-slate-800 overflow-hidden" wire:ignore>
                <div id="admin-dashboard-map" style="height: 460px; width: 100%;"></div>
            </div>

            <!-- Shared Flat Status Tokens Legend (Solid Dot + Text Label) -->
            <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400 pt-1 flex-wrap gap-2">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-hazard-dot)]"></span> Critical Hazard
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-progress-dot)]"></span> In Progress
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-low-dot)]"></span> Low Priority
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-resolved-dot)]"></span> Resolved
                    </span>
                </div>
            </div>
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
                                // Priority Token (Low -> Soft Pink, Medium/High -> Amber, Urgent -> Coral Red)
                                $priorityToken = match($rep->priority) {
                                    'urgent' => ['dot' => 'bg-[var(--color-status-hazard-dot)]', 'bg' => 'bg-[var(--color-status-hazard-bg-light)] dark:bg-[var(--color-status-hazard-bg-dark)]', 'text' => 'text-[var(--color-status-hazard-text-light)] dark:text-[var(--color-status-hazard-text-dark)]', 'label' => 'Critical Hazard'],
                                    'high' => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]', 'label' => 'High'],
                                    'medium' => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]', 'label' => 'Medium'],
                                    default => ['dot' => 'bg-[var(--color-status-low-dot)]', 'bg' => 'bg-[var(--color-status-low-bg-light)] dark:bg-[var(--color-status-low-bg-dark)]', 'text' => 'text-[var(--color-status-low-text-light)] dark:text-[var(--color-status-low-text-dark)]', 'label' => 'Low Priority'],
                                };

                                // Status Token (Resolved -> Green, Rejected -> Coral Red, Active -> Amber)
                                $statusToken = match($rep->status) {
                                    'resolved' => ['dot' => 'bg-[var(--color-status-resolved-dot)]', 'bg' => 'bg-[var(--color-status-resolved-bg-light)] dark:bg-[var(--color-status-resolved-bg-dark)]', 'text' => 'text-[var(--color-status-resolved-text-light)] dark:text-[var(--color-status-resolved-text-dark)]'],
                                    'rejected', 'duplicate' => ['dot' => 'bg-[var(--color-status-hazard-dot)]', 'bg' => 'bg-[var(--color-status-hazard-bg-light)] dark:bg-[var(--color-status-hazard-bg-dark)]', 'text' => 'text-[var(--color-status-hazard-text-light)] dark:text-[var(--color-status-hazard-text-dark)]'],
                                    default => ['dot' => 'bg-[var(--color-status-progress-dot)]', 'bg' => 'bg-[var(--color-status-progress-bg-light)] dark:bg-[var(--color-status-progress-bg-dark)]', 'text' => 'text-[var(--color-status-progress-text-light)] dark:text-[var(--color-status-progress-text-dark)]'],
                                };
                            @endphp
                            <tr
                                wire:key="triage-row-{{ $rep->id }}"
                                wire:click="selectReport({{ $rep->id }})"
                                @click="activeRowIndex = {{ $idx }}"
                                @contextmenu="openContextMenu($event, {{ $rep->id }}, '{{ $rep->reference_number }}', '{{ route('admin.reports.show', $rep->id) }}')"
                                class="cursor-pointer transition {{ $isSelected ? 'bg-teal-50/90 dark:bg-teal-950/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
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

    <!-- Side Triage Drawer (Timeline, Resident Photos, Status Change & Staff Assignment) -->
    @if($selectedReport)
        <div class="fixed inset-y-0 right-0 z-40 w-full max-w-md bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col">
            <!-- Drawer Header -->
            <div class="h-14 px-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div>
                    <span class="font-mono text-xs font-bold text-teal-600 dark:text-teal-400">{{ $selectedReport->reference_number }}</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[260px]">{{ $selectedReport->title }}</h3>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('admin.reports.show', $selectedReport->id) }}" class="px-2 py-1 rounded text-xs font-semibold border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Full Page
                    </a>
                    <button type="button" wire:click="closeDrawer" class="p-1.5 rounded text-slate-400 hover:text-slate-900 dark:hover:text-white">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
                <!-- Issue Description & Location -->
                <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Reporter: <strong class="text-slate-800 dark:text-slate-200">{{ $selectedReport->user?->name ?? 'Resident' }}</strong></span>
                        <span class="tabular-nums-metric">{{ $selectedReport->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <p class="text-slate-800 dark:text-slate-200 leading-relaxed">{{ $selectedReport->description }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                        Location: {{ $selectedReport->address_text }}
                        @if($selectedReport->landmark) ({{ $selectedReport->landmark }}) @endif
                    </p>
                </div>

                <!-- Resident Photos -->
                @if($selectedReport->images->isNotEmpty())
                    <div class="space-y-1.5">
                        <h4 class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Resident Photo Evidence</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($selectedReport->images as $img)
                                <a href="{{ $img->url }}" target="_blank" class="block rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="{{ $img->url }}" alt="Report photo" class="w-full h-28 object-cover">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Triage Controls: Status Change (Shortcut S) & Staff Assignment (Shortcut A) -->
                <form wire:submit="saveTriageUpdate" class="p-3.5 rounded-md border border-slate-200 dark:border-slate-800 space-y-3 bg-white dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">Quick Triage & Assignment</h4>
                        <span class="text-[10px] font-mono text-slate-400">Keys: S / A</span>
                    </div>

                    <div>
                        <label for="drawer-status-select" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Status (Press S)</label>
                        <select id="drawer-status-select" wire:model="drawerStatus" class="w-full py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="verified">Verified</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Unit / Department</label>
                            <select wire:model="drawerDepartmentId" class="w-full py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                                <option value="">Unassigned</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="drawer-assign-select" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Personnel (Press A)</label>
                            <select id="drawer-assign-select" wire:model="drawerStaffId" class="w-full py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                                <option value="">Select Personnel</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Triage / Resolution Note</label>
                        <textarea wire:model="drawerNote" rows="2" placeholder="Add status remark or resolution summary..." class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2 px-3 rounded bg-teal-700 hover:bg-teal-600 text-white font-semibold text-xs transition">
                        Save Triage Update
                    </button>
                </form>

                <!-- Issue Status Timeline -->
                <div class="space-y-2">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Issue Audit Timeline</h4>
                    <div class="space-y-2.5 border-l-2 border-slate-200 dark:border-slate-800 ml-2 pl-3">
                        @foreach($selectedReport->statusHistories as $history)
                            <div class="space-y-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</span>
                                    <span class="text-[10px] text-slate-400 tabular-nums-metric">{{ $history->created_at->format('M d, h:i A') }}</span>
                                </div>
                                @if($history->comment)
                                    <p class="text-slate-600 dark:text-slate-400">{{ $history->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let adminWorkspaceMap = null;
    let adminMarkersById = {};

    document.addEventListener('DOMContentLoaded', function() {
        initAdminMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initAdminMap();
    });

    window.addEventListener('pan-admin-map', function(event) {
        const detail = event.detail;
        if (adminWorkspaceMap && detail && detail.lat && detail.lng) {
            adminWorkspaceMap.flyTo([detail.lat, detail.lng], 16, { duration: 0.45 });
            if (detail.id && adminMarkersById[detail.id]) {
                adminMarkersById[detail.id].openPopup();
            }
        }
    });

    function initAdminMap() {
        const container = document.getElementById('admin-dashboard-map');
        if (!container) return;

        if (adminWorkspaceMap) {
            adminWorkspaceMap.remove();
            adminWorkspaceMap = null;
        }

        const reports = @json($mapReports);

        adminWorkspaceMap = L.map('admin-dashboard-map', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([14.2816, 121.4146], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(adminWorkspaceMap);

        adminMarkersById = {};

        reports.forEach(r => {
            let color = '#d97706'; // In Progress / Active Amber
            if (r.priority === 'urgent' || r.status === 'rejected') color = '#e11d48'; // Critical Hazard Coral Red
            else if (r.status === 'resolved') color = '#059669'; // Resolved Green
            else if (r.priority === 'low' && r.status === 'submitted') color = '#db2777'; // Low Priority Soft Pink

            const icon = L.divIcon({
                className: 'custom-admin-marker',
                html: `<div style="background-color:${color}; width:14px; height:14px; border-radius:50%; border:2px solid #ffffff;"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            const popupContent = `
                <div style="font-family:Inter,sans-serif; min-width:170px;">
                    <div style="font-size:10px; font-weight:700; color:#64748b; font-family:monospace;">${r.ref}</div>
                    <div style="font-size:12px; font-weight:700; color:#0f172a; margin-top:2px;">${r.title}</div>
                    <div style="font-size:11px; color:#475569; margin:3px 0;">${r.category} • ${r.status_label}</div>
                </div>
            `;

            const marker = L.marker([r.lat, r.lng], { icon: icon })
                .addTo(adminWorkspaceMap)
                .bindPopup(popupContent);

            adminMarkersById[r.id] = marker;
        });
    }
</script>
@endpush
