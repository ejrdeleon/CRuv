<div class="space-y-6">
    <!-- Welcome Header & Quick Action -->
    <div class="bg-gradient-to-br from-teal-700 via-teal-800 to-slate-900 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-teal-500/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 space-y-3">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-teal-500/30 text-teal-200 border border-teal-400/20">
                Barangay San Isidro Civic Portal
            </span>
            <h1 class="text-2xl font-bold tracking-tight">Magandang araw, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-teal-100/90 max-w-lg">
                Report damaged roads, fallen electrical wires, clogged drainage, and community hazards directly to barangay officers.
            </p>

            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ route('resident.reports.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-teal-900 font-bold text-sm shadow hover:bg-teal-50 transition active:scale-95">
                    <svg class="w-5 h-5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Report a Problem Now</span>
                </a>
                <a href="{{ route('resident.reports.index') }}" class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-teal-800/80 hover:bg-teal-800 text-white font-semibold text-sm transition">
                    <span>View My Reports</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Emergency Safety Advisory Card -->
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/90 flex items-start gap-3.5 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="text-xs space-y-1">
            <h2 class="font-bold text-amber-950 text-sm">Emergency & Electrical Wire Advisory</h2>
            <p class="text-amber-800 leading-relaxed">
                If you encounter <strong>fallen live electrical lines, open fires, or active flooding risks</strong>, stay at least <strong>10 meters away</strong>. Submitting a report logs municipal coordination; for immediate life-threatening peril, call emergency dispatch <strong>911</strong> or Meralco <strong>16211</strong> immediately.
            </p>
        </div>
    </div>

    <!-- Status Summaries Cards -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 text-center shadow-sm">
            <span class="block text-2xl font-black text-slate-900">{{ $stats['total'] }}</span>
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Filed</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-teal-200 bg-teal-50/30 text-center shadow-sm">
            <span class="block text-2xl font-black text-teal-700">{{ $stats['active'] }}</span>
            <span class="text-xs font-semibold text-teal-800 uppercase tracking-wide">In Progress</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-emerald-200 bg-emerald-50/30 text-center shadow-sm">
            <span class="block text-2xl font-black text-emerald-700">{{ $stats['resolved'] }}</span>
            <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">Resolved</span>
        </div>
    </div>

    <!-- Quick Report Tracker Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Track Report by Reference ID</h2>
        <form wire:submit="trackReport" class="flex items-center gap-2">
            <input type="text" wire:model="trackReference" placeholder="e.g. BRGY-20261021-0001"
                class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2 text-sm uppercase placeholder:normal-case focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition shrink-0">
                Track
            </button>
        </form>
        @if ($trackError)
            <p class="text-xs text-rose-600 mt-2 font-medium">{{ $trackError }}</p>
        @endif
    </div>

    <!-- Common Hazard Categories Quick Access -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Damage Categories</h2>
            <span class="text-xs text-slate-500">Tap to report</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            @foreach ($categories as $cat)
                <a href="{{ route('resident.reports.create', ['category_id' => $cat->id]) }}"
                    class="p-3 bg-white hover:bg-teal-50/50 border border-slate-200 hover:border-teal-300 rounded-xl transition text-left group flex flex-col justify-between shadow-sm">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $cat->is_hazardous ? 'bg-rose-100 text-rose-700' : 'bg-teal-100 text-teal-700' }} mb-2 group-hover:scale-105 transition">
                        @if($cat->is_hazardous)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-slate-800 line-clamp-2 leading-snug">{{ $cat->name }}</span>
                    @if($cat->is_hazardous)
                        <span class="text-[10px] font-bold text-rose-600 mt-1 uppercase">Hazard Alert</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Your Recent Reports</h2>
            @if($recentReports->count() > 0)
                <a href="{{ route('resident.reports.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">See all ({{ $stats['total'] }})</a>
            @endif
        </div>

        @if($recentReports->isEmpty())
            <div class="bg-white rounded-2xl p-8 border border-slate-200 text-center shadow-sm">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">No reports submitted yet</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Found road potholes, broken streetlights, or flooding in your area? Submit a report now to alert the barangay.</p>
                <a href="{{ route('resident.reports.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Submit First Report</span>
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($recentReports as $rep)
                    <a href="{{ route('resident.reports.show', $rep->id) }}" class="block bg-white rounded-2xl p-4 border border-slate-200 hover:border-teal-300 transition shadow-sm group">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-mono font-bold text-slate-500">{{ $rep->reference_number }}</span>
                                    <span class="text-xs text-slate-400">•</span>
                                    <span class="text-xs text-slate-500">{{ $rep->created_at->diffForHumans() }}</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition truncate">{{ $rep->title }}</h3>
                                <p class="text-xs text-slate-500 flex items-center gap-1.5 truncate">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $rep->address_text }}</span>
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                @php
                                    $statusColors = [
                                        'submitted' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'under_review' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'verified' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'assigned' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        'in_progress' => 'bg-teal-50 text-teal-800 border-teal-200',
                                        'resolved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        'duplicate' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                    $badge = $statusColors[$rep->status] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
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
            <h2 class="text-sm font-bold text-slate-900">Recent Updates</h2>
            <div class="space-y-2">
                @foreach ($recentNotifications as $notif)
                    <div class="p-3 bg-white rounded-xl border border-slate-200 text-xs flex items-start justify-between gap-3 shadow-sm">
                        <div class="space-y-0.5">
                            <p class="font-bold text-slate-800">{{ $notif->title }}</p>
                            <p class="text-slate-600">{{ $notif->message }}</p>
                            <span class="text-[10px] text-slate-400 block">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        @if($notif->report_id)
                            <a href="{{ route('resident.reports.show', $notif->report_id) }}" class="text-teal-700 hover:text-teal-800 font-semibold shrink-0">View</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
