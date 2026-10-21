<div class="space-y-6">
    <!-- Header with Export & Print Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Analytics & Civic Exports</h1>
            <p class="text-xs text-slate-500">Generate executive community reports, filter by dates, and export records for barangay council meetings.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
            </button>

            <button type="button" wire:click="exportCsv"
                class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download CSV Export</span>
            </button>
        </div>
    </div>

    <!-- Date Range & Filter Toolbar -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200 shadow-sm space-y-3 print:hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">From Date</label>
                <input type="date" wire:model.live="fromDate" class="w-full p-2 rounded-xl border border-slate-300">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">To Date</label>
                <input type="date" wire:model.live="toDate" class="w-full p-2 rounded-xl border border-slate-300">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Category</label>
                <select wire:model.live="categoryFilter" class="w-full p-2 rounded-xl border border-slate-300 bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Department</label>
                <select wire:model.live="departmentFilter" class="w-full p-2 rounded-xl border border-slate-300 bg-white">
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
        <h2 class="text-lg font-black uppercase tracking-wide">Republic of the Philippines</h2>
        <h3 class="text-base font-bold">Province of Laguna • Municipality of Bay</h3>
        <h1 class="text-xl font-black mt-1">Barangay San Isidro Incident & Infrastructure Report</h1>
        <p class="text-xs text-slate-600 mt-1">Period: {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</p>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm text-center">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Incidents</span>
            <span class="block text-3xl font-black text-slate-900 mt-1">{{ $totalCount }}</span>
            <span class="text-[11px] text-slate-500 mt-1 block">In selected period</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm text-center">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Resolved Cases</span>
            <span class="block text-3xl font-black text-emerald-700 mt-1">{{ $resolvedCount }}</span>
            <span class="text-[11px] text-emerald-600 mt-1 block">Completed repairs</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-teal-200 bg-teal-50/20 shadow-sm text-center">
            <span class="text-xs font-bold text-teal-800 uppercase tracking-wide">Resolution Rate</span>
            <span class="block text-3xl font-black text-teal-700 mt-1">{{ $resolutionRate }}%</span>
            <span class="text-[11px] text-teal-600 mt-1 block">Efficiency ratio</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-rose-200 bg-rose-50/20 shadow-sm text-center">
            <span class="text-xs font-bold text-rose-800 uppercase tracking-wide">Urgent / Critical</span>
            <span class="block text-3xl font-black text-rose-700 mt-1">{{ $urgentCount }}</span>
            <span class="text-[11px] text-rose-600 mt-1 block">High priority cases</span>
        </div>
    </div>

    <!-- Breakdown Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By Category -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
            <h2 class="text-sm font-bold text-slate-900">Cases by Category</h2>
            <div class="space-y-2.5 text-xs">
                @foreach($categoryBreakdown as $cat)
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                        <span class="font-medium text-slate-800">{{ $cat->name }}</span>
                        <span class="font-mono font-bold text-slate-900 bg-white px-2.5 py-0.5 rounded border border-slate-200">
                            {{ $cat->reports_count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- By Barangay Area -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
            <h2 class="text-sm font-bold text-slate-900">Cases by Barangay Jurisdiction</h2>
            <div class="space-y-2.5 text-xs">
                @forelse($barangayCounts as $brgy => $cnt)
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                        <span class="font-medium text-slate-800">{{ $brgy ?: 'Unspecified' }}</span>
                        <span class="font-mono font-bold text-slate-900 bg-white px-2.5 py-0.5 rounded border border-slate-200">
                            {{ $cnt }}
                        </span>
                    </div>
                @empty
                    <p class="text-slate-400 py-4 text-center">No reports in selected range.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Summary Records Table for Printing / Meeting Review -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
        <h2 class="text-sm font-bold text-slate-900">Detailed Report Ledger (Top 50 in Range)</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="pb-2.5">Ref #</th>
                        <th class="pb-2.5">Title & Location</th>
                        <th class="pb-2.5">Category</th>
                        <th class="pb-2.5">Priority</th>
                        <th class="pb-2.5">Status</th>
                        <th class="pb-2.5">Assigned To</th>
                        <th class="pb-2.5">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($reportsList as $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 font-mono font-bold text-slate-600">{{ $r->reference_number }}</td>
                            <td class="py-2.5 max-w-[220px]">
                                <p class="font-bold text-slate-900 truncate">{{ $r->title }}</p>
                                <span class="text-[10px] text-slate-500 truncate block">{{ $r->address_text }}</span>
                            </td>
                            <td class="py-2.5 text-slate-700">{{ $r->category->name }}</td>
                            <td class="py-2.5 font-bold uppercase text-[10px]">{{ $r->priority }}</td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                            <td class="py-2.5 text-slate-600">{{ $r->assignedStaff?->name ?? 'Unassigned' }}</td>
                            <td class="py-2.5 text-slate-500 whitespace-nowrap">{{ $r->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
