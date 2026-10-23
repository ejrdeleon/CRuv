<div class="space-y-4 relative">
    <!-- Header with Views & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Issue Queue & Case Management</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Filter, prioritize, verify, assign units, and inspect cases on the live municipal map.</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Mode Switcher: Split (Default), Table, Map -->
            <div class="inline-flex rounded-md p-0.5 bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs">
                <button type="button" wire:click="$set('viewMode', 'split')"
                    class="px-2.5 py-1 rounded font-medium transition flex items-center gap-1.5 {{ $viewMode === 'split' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                    <span>Split View</span>
                </button>
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

    <!-- Filters Bar (Shared Data Source for Table + Map) -->
    <div class="bg-white dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-800 space-y-2.5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
            <div class="sm:col-span-2 relative">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference #, title, barangay, resident name..."
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

            <div>
                <select wire:model.live="departmentFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 text-xs bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none">
                    <option value="">All Municipal Units</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="personnelFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 text-xs bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none">
                    <option value="">All Personnel</option>
                    @foreach($personnelList as $person)
                        <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->position }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="assignmentFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 text-xs bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none">
                    <option value="">All Assignment States</option>
                    <option value="unassigned">⚠️ Unassigned Only</option>
                    <option value="assigned">✓ Assigned (Any Unit)</option>
                </select>
            </div>
        </div>

        @if($search || $statusFilter || $priorityFilter || $categoryFilter || $departmentFilter || $personnelFilter || $assignmentFilter)
            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100 dark:border-slate-800">
                <span class="text-slate-500 dark:text-slate-400 text-[11px]">
                    Filters active • Showing {{ $reports->total() }} matching {{ \Illuminate\Support\Str::plural('case', $reports->total()) }} ({{ count($mapReports) }} with GPS coordinates)
                </span>
                <button type="button" wire:click="resetFilters" class="text-teal-600 dark:text-teal-400 hover:underline font-semibold text-xs">Reset Filters</button>
            </div>
        @endif
    </div>

    <!-- Main Content: Shared Map Component + Filtered Issue Queue Table -->
    <div class="grid grid-cols-1 {{ $viewMode === 'split' ? 'lg:grid-cols-12' : '' }} gap-4 items-start">
        @if($viewMode === 'split' || $viewMode === 'map')
            <div class="{{ $viewMode === 'split' ? 'lg:col-span-5' : 'w-full' }}">
                <x-admin.spatial-map
                    map-id="issue-queue-map"
                    :markers="$mapReports"
                    :height="$viewMode === 'map' ? '540px' : '480px'"
                    title="Filtered Issue Queue Map"
                    subtitle="Synced with active filters. Click a pin or row to inspect."
                    :show-expand-link="false"
                />
            </div>
        @endif

        @if($viewMode === 'split' || $viewMode === 'table')
            <div class="{{ $viewMode === 'split' ? 'lg:col-span-7' : 'w-full' }} bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
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
                                        $isSelected = $selectedReportId === $report->id;
                                        $hasCoords = $report->latitude !== null && $report->longitude !== null && (abs((float) $report->latitude) > 0.001 || abs((float) $report->longitude) > 0.001);
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
                                    <tr
                                        wire:key="issue-queue-row-{{ $report->id }}"
                                        data-report-row-id="{{ $report->id }}"
                                        wire:click="selectReport({{ $report->id }})"
                                        class="cursor-pointer transition {{ $isSelected ? 'bg-teal-50/90 dark:bg-teal-950/60 ring-1 ring-inset ring-teal-600/40' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/60' }}"
                                    >
                                        <td class="py-2.5 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap text-[11px] tabular-nums-metric">
                                            <a href="{{ route('admin.reports.show', $report->id) }}" @click.stop class="text-teal-600 dark:text-teal-400 hover:underline">
                                                {{ $report->reference_number }}
                                            </a>
                                        </td>
                                        <td class="py-2.5 px-3 max-w-[240px]">
                                            <div class="flex items-center gap-2.5">
                                                @if($report->images->isNotEmpty())
                                                    <x-report-photo :image="$report->images->first()" size="thumb" />
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $report->title }}</p>
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                                        {{ $report->address_text }}
                                                        @if(!$hasCoords)
                                                            <span class="text-amber-600 dark:text-amber-400 font-semibold">(No GPS)</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 max-w-[140px] truncate">
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
                                        <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 max-w-[150px] truncate">
                                            @if($report->personnel)
                                                <span class="font-medium text-slate-900 dark:text-white block truncate">{{ $report->personnel->name }}</span>
                                                <span class="text-[11px] text-teal-700 dark:text-teal-400 font-semibold block truncate">{{ $report->department?->code ?? $report->personnel->department?->code }}</span>
                                            @elseif($report->department)
                                                <span class="font-medium text-slate-900 dark:text-white block truncate">{{ $report->department->code }}</span>
                                                <span class="text-[11px] text-slate-400 block truncate">{{ $report->department->name }}</span>
                                            @else
                                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold block italic">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 whitespace-nowrap text-[11px] tabular-nums-metric">
                                            {{ $report->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right whitespace-nowrap" @click.stop>
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

    <!-- Shared Triage Detail Drawer -->
    <x-admin.triage-drawer
        :selected-report="$selectedReport"
        :departments="$departments"
    />
</div>
