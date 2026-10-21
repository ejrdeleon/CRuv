<div class="space-y-4 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Notifications & Alerts</h1>
            <p class="text-xs text-slate-500">Real-time status updates on your reported community issues.</p>
        </div>
        <button type="button" wire:click="markAllAsRead"
            class="text-xs font-semibold text-teal-700 hover:text-teal-800 bg-teal-50 px-3 py-1.5 rounded-xl border border-teal-200 transition">
            Mark all read
        </button>
    </div>

    @if ($notifications->isEmpty())
        <div class="bg-white rounded-3xl p-10 border border-slate-200 text-center shadow-sm">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">No notifications yet</h3>
            <p class="text-xs text-slate-500 mt-1">You will receive alerts here when the barangay inspects or resolves your reports.</p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($notifications as $n)
                <div class="p-4 rounded-2xl border transition shadow-sm {{ $n->is_read ? 'bg-white border-slate-200' : 'bg-teal-50/40 border-teal-200 ring-1 ring-teal-500/20' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">{{ $n->title }}</span>
                                @if(!$n->is_read)
                                    <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $n->message }}</p>
                            <span class="text-[11px] text-slate-400 block pt-1">{{ $n->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($n->report_id)
                                <a href="{{ route('resident.reports.show', $n->report_id) }}" wire:click="markAsRead({{ $n->id }})"
                                    class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-sm transition">
                                    View
                                </a>
                            @endif

                            @if(!$n->is_read)
                                <button type="button" wire:click="markAsRead({{ $n->id }})" title="Mark as read"
                                    class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
