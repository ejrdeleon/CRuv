<div class="space-y-4">
    <!-- Header with Export & Print Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Incident Analytics & Records Export</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Decision-support metrics, period filtering, and printable CSV/ledger exports.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Ledger</span>
            </button>

            <button type="button" wire:click="exportCsv"
                class="px-3.5 py-1.5 rounded-md bg-teal-700 hover:bg-teal-600 text-white font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <!-- Date Range & Filter Toolbar -->
    <div class="bg-white dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-800 space-y-2 print:hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px]">From Date</label>
                <input type="date" wire:model.live="fromDate" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px]">To Date</label>
                <input type="date" wire:model.live="toDate" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px]">Category</label>
                <select wire:model.live="categoryFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px]">Department</label>
                <select wire:model.live="departmentFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics (Tabular Numerals) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Logged</span>
            <span class="block text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5 tabular-nums-metric">{{ $totalCount }}</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Resolved</span>
            <span class="block text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5 tabular-nums-metric">{{ $resolvedCount }}</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Resolution Rate</span>
            <span class="block text-2xl font-extrabold text-teal-600 dark:text-teal-400 mt-0.5 tabular-nums-metric">{{ $resolutionRate }}%</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Critical Hazards</span>
            <span class="block text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-0.5 tabular-nums-metric">{{ $urgentCount }}</span>
        </div>
    </div>

    <!-- Single Decision-Support Visual Breakdown: Category Demand Distribution -->
    <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Category Demand Distribution</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Identifies which municipal maintenance units require resource allocation.</p>
            </div>
        </div>
        <div class="space-y-2 text-xs">
            @php $maxCatCount = max(1, $categoryBreakdown->max('reports_count')); @endphp
            @foreach($categoryBreakdown as $cat)
                @php $pct = round(($cat->reports_count / $maxCatCount) * 100); @endphp
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $cat->name }}</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white tabular-nums-metric">{{ $cat->reports_count }}</span>
                    </div>
                    <div class="w-full h-2 rounded bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full {{ $cat->is_hazardous ? 'bg-[var(--color-status-hazard-dot)]' : 'bg-teal-600' }}" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Summary Records Table -->
    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-3 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Detailed Case Ledger (Top 50 in Range)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2 px-3">Ref #</th>
                        <th class="py-2 px-3">Title & Location</th>
                        <th class="py-2 px-3">Category</th>
                        <th class="py-2 px-3">Priority</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Assigned Unit</th>
                        <th class="py-2 px-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach($reportsList as $r)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60 transition">
                            <td class="py-2 px-3 font-mono font-semibold text-slate-600 dark:text-slate-300 text-[11px] tabular-nums-metric">{{ $r->reference_number }}</td>
                            <td class="py-2 px-3 max-w-[220px]">
                                <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $r->title }}</p>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate block">{{ $r->address_text }}</span>
                            </td>
                            <td class="py-2 px-3 text-slate-700 dark:text-slate-300">{{ $r->category->name }}</td>
                            <td class="py-2 px-3 font-semibold uppercase text-[10px] text-slate-700 dark:text-slate-300">{{ $r->priority }}</td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-slate-600 dark:text-slate-400 text-[11px]">
                                {{ $r->department?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 dark:text-slate-400 text-[11px] tabular-nums-metric">
                                {{ $r->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
