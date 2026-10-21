<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">My Submitted Reports</h1>
            <p class="text-xs text-slate-500">Track current status and barangay actions for your community reports.</p>
        </div>
        <a href="{{ route('resident.reports.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Report</span>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-sm space-y-3">
        <!-- Search bar -->
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title, reference #, or address..."
                class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
        </div>

        <!-- Filter Tabs -->
        <div class="flex gap-1.5 overflow-x-auto pb-1 text-xs">
            <button type="button" wire:click="$set('statusFilter', 'all')"
                class="px-3 py-1.5 rounded-lg font-semibold shrink-0 transition {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Reports
            </button>
            <button type="button" wire:click="$set('statusFilter', 'active')"
                class="px-3 py-1.5 rounded-lg font-semibold shrink-0 transition {{ $statusFilter === 'active' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Active
            </button>
            <button type="button" wire:click="$set('statusFilter', 'in_progress')"
                class="px-3 py-1.5 rounded-lg font-semibold shrink-0 transition {{ $statusFilter === 'in_progress' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                In Progress
            </button>
            <button type="button" wire:click="$set('statusFilter', 'resolved')"
                class="px-3 py-1.5 rounded-lg font-semibold shrink-0 transition {{ $statusFilter === 'resolved' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Resolved
            </button>
        </div>
    </div>

    <!-- Reports List -->
    @if ($reports->isEmpty())
        <div class="bg-white rounded-2xl p-10 border border-slate-200 text-center shadow-sm">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">No matching reports</h3>
            <p class="text-xs text-slate-500 mt-1">Try modifying your filter or submit a new incident report.</p>
        </div>
    @else
        <div class="space-y-3">
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
                        'medium' => 'text-blue-600',
                        'high' => 'text-amber-600 font-bold',
                        'urgent' => 'text-rose-600 font-black',
                    ];
                    $latestPublicComment = $report->publicComments->last();
                @endphp

                <a href="{{ route('resident.reports.show', $report->id) }}"
                    class="block bg-white rounded-2xl p-4 border border-slate-200 hover:border-teal-400 transition shadow-sm group">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11px] font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $report->reference_number }}
                                </span>
                                <span class="text-xs font-semibold text-teal-700 bg-teal-50 px-2 py-0.5 rounded">
                                    {{ $report->category->name }}
                                </span>
                                <span class="text-[11px] uppercase font-bold {{ $priorityColors[$report->priority] ?? 'text-slate-500' }}">
                                    {{ $report->priority }} Priority
                                </span>
                            </div>

                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition leading-snug">
                                {{ $report->title }}
                            </h3>

                            <p class="text-xs text-slate-500 flex items-center gap-1.5 truncate">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $report->address_text }}, {{ $report->barangay }}</span>
                            </p>

                            <!-- Latest public update snippet if exists -->
                            @if ($latestPublicComment)
                                <div class="mt-2 p-2 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 flex items-start gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-teal-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    <span class="line-clamp-1"><strong>Update:</strong> {{ $latestPublicComment->comment }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Status badge & date -->
                        <div class="text-right shrink-0 space-y-2">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusStyles[$report->status] ?? 'bg-slate-100 text-slate-700' }}">
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
