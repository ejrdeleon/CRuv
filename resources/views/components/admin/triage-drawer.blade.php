@props([
    'selectedReport' => null,
    'departments' => [],
    'staffMembers' => [],
])

@if($selectedReport)
    <div class="fixed inset-y-0 right-0 z-40 w-full max-w-md bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-lg flex flex-col">
        <!-- Drawer Header -->
        <div class="h-14 px-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
            <div>
                <span class="font-mono text-xs font-bold text-teal-600 dark:text-teal-400">{{ $selectedReport->reference_number }}</span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[260px]">{{ $selectedReport->title }}</h3>
            </div>
            <div class="flex items-center gap-1.5">
                <a href="{{ route('admin.reports.show', $selectedReport->id) }}" class="px-2 py-1 rounded text-xs font-semibold border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    Full Page
                </a>
                <button type="button" wire:click="closeDrawer" class="p-1.5 rounded text-slate-400 hover:text-slate-900 dark:hover:text-white">
                    ✕
                </button>
            </div>
        </div>

        <!-- Drawer Body -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
            <!-- Issue Description & Location -->
            <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                    <span>Reporter: <strong class="text-slate-800 dark:text-slate-200">{{ $selectedReport->user?->name ?? 'Resident' }}</strong></span>
                    <span class="tabular-nums-metric">{{ $selectedReport->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <p class="text-slate-800 dark:text-slate-200 leading-relaxed">{{ $selectedReport->description }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                    Location: {{ $selectedReport->address_text }}
                    @if($selectedReport->barangay) • Brgy. {{ $selectedReport->barangay }} @endif
                    @if($selectedReport->landmark) ({{ $selectedReport->landmark }}) @endif
                </p>
                @if($selectedReport->latitude && $selectedReport->longitude && (abs((float) $selectedReport->latitude) > 0.001 || abs((float) $selectedReport->longitude) > 0.001))
                    <p class="text-[10.5px] font-mono text-slate-400">
                        Coords: {{ $selectedReport->latitude }}, {{ $selectedReport->longitude }}
                    </p>
                @else
                    <p class="text-[10.5px] text-amber-600 dark:text-amber-400 font-medium">
                        {{ $this->mapCoordWarning ?? 'No valid GPS coordinates attached to this report.' }}
                    </p>
                @endif
            </div>

            <!-- Resident Photos -->
            @if($selectedReport->images->isNotEmpty())
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Resident Photo Evidence ({{ $selectedReport->images->count() }})</h4>
                        <span class="text-[10px] text-slate-400">Click to zoom</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($selectedReport->images as $img)
                            <x-report-photo :image="$img" size="drawer" />
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Triage Controls: Status Change (Shortcut S) & Staff Assignment (Shortcut A) -->
            <form wire:submit="saveTriageUpdate" class="p-3.5 rounded-md border border-slate-200 dark:border-slate-800 space-y-3 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">Quick Triage & Assignment</h4>
                    <span class="text-[10px] font-mono text-slate-400">Keys: S / A</span>
                </div>

                <div>
                    <label for="drawer-status-select" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Status (Press S)</label>
                    <select id="drawer-status-select" wire:model="drawerStatus" class="w-full py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="verified">Verified</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Municipal Unit</label>
                        <select wire:model.live="drawerDepartmentId" class="w-full py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            <option value="">Unassigned</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="drawer-assign-select" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Unit Personnel (Optional)</label>
                        @php
                            $targetDeptId = $this->drawerDepartmentId ?? null;
                            $activePersonnel = $targetDeptId
                                ? \App\Models\Personnel::where('department_id', $targetDeptId)->where('is_active', true)->orderBy('name')->get()
                                : collect();
                        @endphp
                        <select id="drawer-assign-select" wire:model="drawerPersonnelId" class="w-full py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs" {{ ! $targetDeptId ? 'disabled' : '' }}>
                            <option value="">{{ $targetDeptId ? 'All Unit Staff (No specific personnel)' : 'Select Unit First' }}</option>
                            @foreach($activePersonnel as $person)
                                <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->position }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Triage / Resolution Note</label>
                    <textarea wire:model="drawerNote" rows="2" placeholder="Add status remark or resolution summary..." class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs"></textarea>
                </div>

                <button type="submit" class="w-full py-2 px-3 rounded bg-teal-700 hover:bg-teal-600 text-white font-semibold text-xs transition">
                    Save Triage Update
                </button>
            </form>

            <!-- Issue Status Timeline -->
            <div class="space-y-2">
                <h4 class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Issue Audit Timeline</h4>
                <div class="space-y-2.5 border-l-2 border-slate-200 dark:border-slate-800 ml-2 pl-3">
                    @foreach($selectedReport->statusHistories as $history)
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</span>
                                <span class="text-[10px] text-slate-400 tabular-nums-metric">{{ $history->created_at->format('M d, h:i A') }}</span>
                            </div>
                            @if($history->comment)
                                <p class="text-slate-600 dark:text-slate-400">{{ $history->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
