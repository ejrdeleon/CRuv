<div class="space-y-4">
    <!-- Top Filter Controls -->
    <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800 space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">Geospatial Incident Map</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Live geographic distribution of municipal reports across {{ config('community.municipality', 'Santa Cruz') }}, {{ config('community.province', 'Laguna') }}.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search reference or title..."
                class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 px-2.5 py-1.5 text-xs focus:border-teal-700 focus:outline-none">

            <select wire:model.live="categoryFilter" class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 px-2.5 py-1.5 text-xs focus:border-teal-700 focus:outline-none">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 px-2.5 py-1.5 text-xs focus:border-teal-700 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="submitted">Submitted</option>
                <option value="under_review">Under Review</option>
                <option value="verified">Verified</option>
                <option value="assigned">Assigned</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>

            <select wire:model.live="priorityFilter" class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 px-2.5 py-1.5 text-xs focus:border-teal-700 focus:outline-none">
                <option value="">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>

            <button type="button" wire:click="resetFilters" class="px-2.5 py-1.5 rounded-md bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold border border-slate-300 dark:border-slate-700 transition text-center text-xs">
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Map & Side Details Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <!-- Shared Interactive Map Component -->
        <div class="lg:col-span-2">
            <x-admin.spatial-map
                map-id="full-interactive-map"
                :markers="$mapMarkers"
                height="560px"
                title="Santa Cruz Municipal Map"
                subtitle="Click a pin or sidebar item to inspect case details."
                :show-expand-link="false"
            />
        </div>

        <!-- Sidebar List / Selected Report Detail -->
        <div class="space-y-3">
            @if($selectedReport)
                <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-teal-600 dark:border-teal-500 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                        <span class="text-xs font-mono font-bold text-teal-800 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/50 px-2 py-0.5 rounded border border-teal-200 dark:border-teal-800">
                            {{ $selectedReport->reference_number }}
                        </span>
                        <button type="button" wire:click="clearSelectedReport" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-base font-bold leading-none">✕</button>
                    </div>

                    @if($selectedReport->images->first())
                        <x-report-photo :image="$selectedReport->images->first()" size="preview" />
                    @endif

                    <div class="space-y-0.5">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-snug">{{ $selectedReport->title }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $selectedReport->category->name }}</p>
                    </div>

                    <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded border border-slate-200 dark:border-slate-700 text-xs space-y-0.5 text-slate-700 dark:text-slate-300">
                        <p class="font-medium truncate">{{ $selectedReport->address_text }}</p>
                        <p class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $selectedReport->latitude }}, {{ $selectedReport->longitude }}</p>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                            {{ $selectedReport->status_label }}
                        </span>
                        <span class="font-semibold uppercase text-[10px] {{ $selectedReport->priority === 'urgent' ? 'text-rose-700 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                            {{ $selectedReport->priority }} Priority
                        </span>
                    </div>

                    <a href="{{ route('admin.reports.show', $selectedReport->id) }}"
                        class="block w-full py-2 px-3 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs text-center transition">
                        Open Report Dossier →
                    </a>
                </div>
            @endif

            <!-- List of Pins in this View -->
            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 space-y-0 max-h-[560px] overflow-y-auto">
                <div class="p-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white dark:bg-slate-900 z-10">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Pins in View ({{ $reports->count() }})</h3>
                    <span class="text-[10px] text-slate-400">Select to highlight</span>
                </div>

                @if($reports->isEmpty())
                    <p class="text-xs text-slate-400 py-6 text-center">No matching pins found on map.</p>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($reports as $r)
                            <div
                                data-report-row-id="{{ $r->id }}"
                                wire:click="selectReport({{ $r->id }})"
                                class="p-3 text-xs cursor-pointer transition {{ $selectedReportId === $r->id ? 'bg-teal-50/60 dark:bg-teal-950/40 font-medium' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}"
                            >
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="font-mono text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{ $r->reference_number }}</span>
                                    <span class="text-[10px] uppercase font-semibold {{ $r->priority === 'urgent' ? 'text-rose-700 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">
                                        {{ $r->priority }}
                                    </span>
                                </div>
                                <p class="font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $r->title }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $r->address_text }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
