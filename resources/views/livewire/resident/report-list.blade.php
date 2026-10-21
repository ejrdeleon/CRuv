<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900">My Incident Reports</h1>
            <p class="text-xs text-slate-500">Track barangay review progress, field assignments, and resolution notes.</p>
        </div>
        <a href="{{ route('resident.reports.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ New Report</span>
        </a>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-lg p-3 border border-slate-200 shadow-xs space-y-2.5">
        <!-- Search bar -->
        <div class="relative">
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter by title, reference #, or street address..."
                class="w-full pl-8 pr-3 py-1.5 rounded-md border border-slate-300 text-xs focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
        </div>

        <!-- Filter Tabs -->
        <div class="flex gap-1.5 overflow-x-auto text-xs pb-0.5">
            <button type="button" wire:click="$set('statusFilter', 'all')"
                class="px-2.5 py-1 rounded-md font-semibold text-xs shrink-0 transition {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Reports
            </button>
            <button type="button" wire:click="$set('statusFilter', 'active')"
                class="px-2.5 py-1 rounded-md font-semibold text-xs shrink-0 transition {{ $statusFilter === 'active' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Active
            </button>
            <button type="button" wire:click="$set('statusFilter', 'in_progress')"
                class="px-2.5 py-1 rounded-md font-semibold text-xs shrink-0 transition {{ $statusFilter === 'in_progress' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                In Progress
            </button>
            <button type="button" wire:click="$set('statusFilter', 'resolved')"
                class="px-2.5 py-1 rounded-md font-semibold text-xs shrink-0 transition {{ $statusFilter === 'resolved' ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Resolved
            </button>
        </div>
    </div>

    <!-- Reports List -->
    @if ($reports->isEmpty())
        <div class="bg-white rounded-lg p-8 border border-slate-200 text-center shadow-xs">
            <p class="text-xs font-semibold text-slate-800">No reports found</p>
            <p class="text-xs text-slate-500 mt-1">Try modifying your filter or submit a new incident report.</p>
        </div>
    @else
        <div class="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100 shadow-xs">
            @foreach ($reports as $report)
                @php
                    $statusStyles = [
                        'submitted' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'under_review' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'verified' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'assigned' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                        'in_progress' => 'bg-teal-50 text-teal-800 border-teal-200',
                        'resolved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                        'duplicate' => 'bg-slate-100 text-slate-600 border-slate-200',
                    ];
                    $priorityColors = [
                        'low' => 'text-slate-500',
                        'medium' => 'text-blue-700',
                        'high' => 'text-amber-700 font-bold',
                        'urgent' => 'text-rose-700 font-bold',
                    ];
                    $latestPublicComment = $report->publicComments->last();
                @endphp

                <a href="{{ route('resident.reports.show', $report->id) }}"
                    class="block p-3.5 hover:bg-slate-50 transition group">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11px] font-mono font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                    {{ $report->reference_number }}
                                </span>
                                <span class="text-[11px] font-medium text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                    {{ $report->category->name }}
                                </span>
                                <span class="text-[11px] uppercase font-semibold {{ $priorityColors[$report->priority] ?? 'text-slate-500' }}">
                                    {{ $report->priority }}
                                </span>
                            </div>

                            <h3 class="text-xs font-bold text-slate-900 group-hover:text-teal-800 leading-snug">
                                {{ $report->title }}
                            </h3>

                            <p class="text-[11px] text-slate-500 truncate">
                                {{ $report->address_text }}, {{ $report->barangay }}
                            </p>

                            <!-- Latest public update snippet if exists -->
                            @if ($latestPublicComment)
                                <div class="mt-1.5 p-2 bg-slate-50 rounded border border-slate-200 text-[11px] text-slate-700">
                                    <span class="font-semibold text-slate-900">Latest Update:</span> {{ $latestPublicComment->comment }}
                                </div>
                            @endif
                        </div>

                        <!-- Status badge & date -->
                        <div class="text-right shrink-0 space-y-1">
                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold border {{ $statusStyles[$report->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $report->status_label }}
                            </span>
                            <span class="block text-[11px] text-slate-400">
                                {{ $report->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $reports->links() }}
        </div>
    @endif
</div>
