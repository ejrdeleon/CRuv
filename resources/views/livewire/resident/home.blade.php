<div class="space-y-4">
    <!-- Welcome Header & Quick Action -->
    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 mb-1.5">
                    {{ config('community.full_location', 'Santa Cruz, Laguna, Philippines') }} Portal
                </span>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Incident & Hazard Reporting</h1>
                <p class="text-xs text-slate-600 mt-1 max-w-xl">
                    Submit damage reports for roads, electrical hazards, drainage, streetlights, or waste directly to municipal staff.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('resident.reports.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>File a Report</span>
                </a>
                <a href="{{ route('resident.reports.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 transition">
                    <span>My Reports</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Emergency Safety Advisory Card -->
    <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
        <div class="w-6 h-6 rounded bg-amber-200 text-amber-900 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">
            !
        </div>
        <div class="space-y-0.5 leading-relaxed">
            <span class="font-bold">Life-Threatening Emergency Notice:</span>
            <p class="text-amber-800">
                For fallen live electrical lines, open fires, or critical flooding risks, keep a safe distance (at least 10 meters). Call emergency hotline <strong>911</strong> or Meralco <strong>16211</strong> immediately before logging non-urgent municipal follow-ups here.
            </p>
        </div>
    </div>

    <!-- Status Summaries Strip -->
    <div class="bg-white rounded-lg border border-slate-200 grid grid-cols-3 divide-x divide-slate-200 text-center shadow-xs">
        <div class="p-3 sm:p-4">
            <span class="block text-xl sm:text-2xl font-bold text-slate-900">{{ $stats['total'] }}</span>
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">Total Filed</span>
        </div>
        <div class="p-3 sm:p-4">
            <span class="block text-xl sm:text-2xl font-bold text-teal-800">{{ $stats['active'] }}</span>
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">In Progress</span>
        </div>
        <div class="p-3 sm:p-4">
            <span class="block text-xl sm:text-2xl font-bold text-emerald-700">{{ $stats['resolved'] }}</span>
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">Resolved</span>
        </div>
    </div>

    <!-- Quick Report Tracker Search -->
    <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
            <label for="track-ref-input" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Track Reference ID</label>
            <span class="text-[11px] text-slate-500">Check current review status</span>
        </div>
        <form wire:submit="trackReport" class="flex items-center gap-2">
            <input id="track-ref-input" type="text" wire:model="trackReference" placeholder="e.g. BRGY-20261021-0001"
                class="flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-xs uppercase placeholder:normal-case focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
            <button type="submit" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-xs font-semibold transition shrink-0">
                Track
            </button>
        </form>
        @if ($trackError)
            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $trackError }}</p>
        @endif
    </div>

    <!-- Common Hazard Categories Quick Access -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Report By Category</h2>
            <span class="text-[11px] text-slate-500">Select to file incident</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach ($categories as $cat)
                <a href="{{ route('resident.reports.create', ['category_id' => $cat->id]) }}"
                    class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-md transition text-left flex flex-col justify-between group shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-6 h-6 rounded flex items-center justify-center {{ $cat->is_hazardous ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }} text-xs font-bold">
                            @if($cat->is_hazardous)
                                !
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            @endif
                        </span>
                        @if($cat->is_hazardous)
                            <span class="text-[10px] font-semibold text-rose-700 uppercase">Hazard</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-900 group-hover:text-teal-800 leading-snug block line-clamp-1">{{ $cat->name }}</span>
                        <span class="text-[10px] text-slate-500">{{ $cat->sla_hours }}h SLA target</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Your Recent Submissions</h2>
            @if($recentReports->count() > 0)
                <a href="{{ route('resident.reports.index') }}" class="text-xs font-semibold text-teal-800 hover:underline">View all ({{ $stats['total'] }})</a>
            @endif
        </div>

        @if($recentReports->isEmpty())
            <div class="bg-white rounded-lg p-6 border border-slate-200 text-center shadow-xs">
                <p class="text-xs font-semibold text-slate-800">No reports recorded</p>
                <p class="text-xs text-slate-500 mt-1">You have not submitted any incident or damage reports yet.</p>
                <a href="{{ route('resident.reports.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs rounded-md shadow-xs transition">
                    + Submit a Report
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100 shadow-xs">
                @foreach ($recentReports as $rep)
                    <a href="{{ route('resident.reports.show', $rep->id) }}" class="block p-3.5 hover:bg-slate-50 transition group">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-0.5 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-mono font-semibold text-slate-600">{{ $rep->reference_number }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-[11px] text-slate-500">{{ $rep->created_at->format('M d, Y') }}</span>
                                </div>
                                <h3 class="text-xs font-bold text-slate-900 group-hover:text-teal-800 truncate">{{ $rep->title }}</h3>
                                <p class="text-[11px] text-slate-500 truncate">{{ $rep->address_text }}</p>
                            </div>

                            <div class="text-right shrink-0">
                                @php
                                    $statusClasses = [
                                        'submitted' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'under_review' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'verified' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'assigned' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        'in_progress' => 'bg-teal-50 text-teal-800 border-teal-200',
                                        'resolved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        'duplicate' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                    $badge = $statusClasses[$rep->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold border {{ $badge }}">
                                    {{ $rep->status_label }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recent Alerts / Notifications -->
    @if($recentNotifications->isNotEmpty())
        <div class="space-y-2">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Recent Activity & Notifications</h2>
            <div class="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100 shadow-xs">
                @foreach ($recentNotifications as $notif)
                    <div class="p-3 text-xs flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <p class="font-bold text-slate-800">{{ $notif->title }}</p>
                            <p class="text-slate-600 leading-snug">{{ $notif->message }}</p>
                            <span class="text-[10px] text-slate-400 block">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        @if($notif->report_id)
                            <a href="{{ route('resident.reports.show', $notif->report_id) }}" class="text-teal-800 hover:underline font-semibold text-[11px] shrink-0">View</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
