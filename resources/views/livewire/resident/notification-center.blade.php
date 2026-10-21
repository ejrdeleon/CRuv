<div class="space-y-4 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900">Notifications & Alerts</h1>
            <p class="text-xs text-slate-500">Status updates and field dispatch notices regarding your reports.</p>
        </div>
        <button type="button" wire:click="markAllAsRead"
            class="text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white px-2.5 py-1 rounded-md border border-slate-300 transition">
            Mark all read
        </button>
    </div>

    @if ($notifications->isEmpty())
        <div class="bg-white rounded-lg p-8 border border-slate-200 text-center shadow-xs">
            <p class="text-xs font-semibold text-slate-800">No notifications</p>
            <p class="text-xs text-slate-500 mt-1">You will receive notifications here as your incident reports are reviewed and updated.</p>
        </div>
    @else
        <div class="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100 shadow-xs">
            @foreach ($notifications as $n)
                <div class="p-3.5 {{ $n->is_read ? 'bg-white' : 'bg-teal-50/30' }} transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-0.5 min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-slate-900">{{ $n->title }}</span>
                                @if(!$n->is_read)
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-800 shrink-0"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $n->message }}</p>
                            <span class="text-[11px] text-slate-400 block pt-0.5">{{ $n->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($n->report_id)
                                <a href="{{ route('resident.reports.show', $n->report_id) }}" wire:click="markAsRead({{ $n->id }})"
                                    class="px-2.5 py-1 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition">
                                    View
                                </a>
                            @endif

                            @if(!$n->is_read)
                                <button type="button" wire:click="markAsRead({{ $n->id }})" title="Mark as read"
                                    class="p-1 text-slate-400 hover:text-slate-600 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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
