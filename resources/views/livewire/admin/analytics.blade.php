<div class="space-y-4">
    <!-- Header with Export & Print Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900">Incident Analytics & Records Export</h1>
            <p class="text-xs text-slate-500">Summary statistics, period filtering, and printable reports for barangay council meetings.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="px-3 py-1.5 rounded-md border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Ledger</span>
            </button>

            <button type="button" wire:click="exportCsv"
                class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <!-- Date Range & Filter Toolbar -->
    <div class="bg-white rounded-lg p-3 border border-slate-200 shadow-xs space-y-2 print:hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 uppercase mb-1 text-[11px]">From Date</label>
                <input type="date" wire:model.live="fromDate" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase mb-1 text-[11px]">To Date</label>
                <input type="date" wire:model.live="toDate" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase mb-1 text-[11px]">Category</label>
                <select wire:model.live="categoryFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 bg-white text-xs">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase mb-1 text-[11px]">Department</label>
                <select wire:model.live="departmentFilter" class="w-full py-1.5 px-2.5 rounded-md border border-slate-300 bg-white text-xs">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Printable Official Header -->
    <div class="hidden print:block text-center border-b pb-4 mb-4">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">Republic of the Philippines • Municipality of Bay, Laguna</h2>
        <h1 class="text-lg font-bold text-slate-900 mt-0.5">Barangay San Isidro Incident & Infrastructure Log</h1>
        <p class="text-xs text-slate-500 mt-0.5">Reporting Window: {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</p>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs text-center">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total Logged</span>
            <span class="block text-2xl font-bold text-slate-900 mt-0.5">{{ $totalCount }}</span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">In selected period</span>
        </div>

        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs text-center">
            <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wide">Resolved</span>
            <span class="block text-2xl font-bold text-emerald-700 mt-0.5">{{ $resolvedCount }}</span>
            <span class="text-[10px] text-emerald-600 mt-0.5 block">Completed works</span>
        </div>

        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs text-center">
            <span class="text-[11px] font-semibold text-teal-800 uppercase tracking-wide">Resolution Rate</span>
            <span class="block text-2xl font-bold text-teal-800 mt-0.5">{{ $resolutionRate }}%</span>
            <span class="text-[10px] text-teal-700 mt-0.5 block">Closure ratio</span>
        </div>

        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs text-center">
            <span class="text-[11px] font-semibold text-rose-800 uppercase tracking-wide">Critical Hazards</span>
            <span class="block text-2xl font-bold text-rose-700 mt-0.5">{{ $urgentCount }}</span>
            <span class="text-[10px] text-rose-600 mt-0.5 block">Urgent priority</span>
        </div>
    </div>

    <!-- Breakdown Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- By Category -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs space-y-2">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Volume by Category</h2>
            <div class="space-y-1 text-xs">
                @foreach($categoryBreakdown as $cat)
                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded border border-slate-200">
                        <span class="font-medium text-slate-800">{{ $cat->name }}</span>
                        <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200 text-xs">
                            {{ $cat->reports_count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- By Barangay Area -->
        <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs space-y-2">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Distribution by Sector / Barangay</h2>
            <div class="space-y-1 text-xs">
                @forelse($barangayCounts as $brgy => $cnt)
                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded border border-slate-200">
                        <span class="font-medium text-slate-800">{{ $brgy ?: 'Unspecified' }}</span>
                        <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200 text-xs">
                            {{ $cnt }}
                        </span>
                    </div>
                @empty
                    <p class="text-slate-400 py-3 text-center text-xs">No records in selected range.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Summary Records Table for Printing / Meeting Review -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs space-y-0 overflow-hidden">
        <div class="p-3 border-b border-slate-200">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Detailed Case Ledger (Top 50 in Range)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
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
                <tbody class="divide-y divide-slate-100">
                    @foreach($reportsList as $r)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2 px-3 font-mono font-semibold text-slate-600 text-[11px]">{{ $r->reference_number }}</td>
                            <td class="py-2 px-3 max-w-[220px]">
                                <p class="font-semibold text-slate-900 truncate">{{ $r->title }}</p>
                                <span class="text-[10px] text-slate-500 truncate block">{{ $r->address_text }}</span>
                            </td>
                            <td class="py-2 px-3 text-slate-700">{{ $r->category->name }}</td>
                            <td class="py-2 px-3 font-semibold uppercase text-[10px]">{{ $r->priority }}</td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium border border-slate-200 bg-slate-100 text-slate-800">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-slate-600 text-[11px]">
                                {{ $r->department?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 text-[11px]">
                                {{ $r->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
