<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Top Back Bar & Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('resident.reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Back to My Reports</span>
        </a>

        @if($report->canBeEditedBy(auth()->user()) && !$isEditing)
            <button type="button" wire:click="startEdit"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 border border-teal-200 text-xs font-semibold hover:bg-teal-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Report</span>
            </button>
        @endif
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-6">
        <!-- Reference & Status Header -->
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-mono font-bold text-teal-800 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200">
                        {{ $report->reference_number }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500">
                        Filed {{ $report->created_at->format('M d, Y • h:i A') }}
                    </span>
                </div>

                @if(!$isEditing)
                    <h1 class="text-lg font-bold text-slate-900 leading-snug">{{ $report->title }}</h1>
                @endif
            </div>

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
            @endphp
            <div class="text-right shrink-0">
                <span class="inline-block px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusColors[$report->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $report->status_label }}
                </span>
                <span class="block text-[11px] uppercase font-bold text-slate-400 mt-1">
                    {{ $report->priority }} Priority
                </span>
            </div>
        </div>

        <!-- Inline Edit Mode -->
        @if($isEditing)
            <form wire:submit="saveEdit" class="space-y-4 p-4 bg-teal-50/40 rounded-2xl border border-teal-200">
                <h3 class="text-xs font-bold text-teal-900 uppercase">Edit Report Information</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Title</label>
                    <input type="text" wire:model="editTitle" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    @error('editTitle') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Description</label>
                    <textarea wire:model="editDescription" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                    @error('editDescription') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Landmark</label>
                    <input type="text" wire:model="editLandmark" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" wire:click="cancelEdit" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm">Save Changes</button>
                </div>
            </form>
        @endif

        <!-- Progress Timeline Bar -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Report Progress Stage</h2>
            @php
                $stages = ['submitted', 'verified', 'assigned', 'in_progress', 'resolved'];
                $stageLabels = [
                    'submitted' => 'Submitted',
                    'verified' => 'Verified',
                    'assigned' => 'Assigned',
                    'in_progress' => 'In Progress',
                    'resolved' => 'Resolved',
                ];

                $currentStatus = $report->status;
                $currentIndex = array_search($currentStatus, $stages);
                if ($currentStatus === 'under_review') $currentIndex = 0.5;
                if ($currentStatus === 'rejected' || $currentStatus === 'duplicate') $currentIndex = -1;
            @endphp

            @if($currentStatus === 'rejected')
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
                    <p class="font-bold">This report was rejected by barangay administration.</p>
                    @if($report->rejection_reason)
                        <p class="mt-1">Reason: {{ $report->rejection_reason }}</p>
                    @endif
                </div>
            @elseif($currentStatus === 'duplicate')
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900">
                    <p class="font-bold">This report has been linked to an existing case.</p>
                    @if($report->duplicate_of_id)
                        <p class="mt-1">Primary Case: <a href="{{ route('resident.reports.show', $report->duplicate_of_id) }}" class="underline font-bold">#{{ $report->duplicateOf?->reference_number }}</a></p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-5 gap-1 relative pt-2">
                    @foreach($stages as $idx => $st)
                        @php
                            $isComplete = $currentIndex !== false && $currentIndex >= $idx;
                            $isCurrent = $currentStatus === $st;
                        @endphp
                        <div class="flex flex-col items-center text-center">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $isComplete ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-500' }} {{ $isCurrent ? 'ring-4 ring-teal-100 shadow' : '' }}">
                                @if($isComplete)
                                    ✓
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </div>
                            <span class="text-[10px] font-semibold mt-1 {{ $isComplete ? 'text-teal-900' : 'text-slate-400' }}">
                                {{ $stageLabels[$st] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Category</span>
                <p class="font-bold text-slate-800">{{ $report->category->name }}</p>
                @if($report->category->department)
                    <p class="text-slate-500 text-[11px]">Handled by: {{ $report->category->department->name }}</p>
                @endif
            </div>

            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Assigned Staff</span>
                <p class="font-bold text-slate-800">
                    {{ $report->assignedStaff ? $report->assignedStaff->name : 'Awaiting Assignment' }}
                </p>
                @if($report->department)
                    <p class="text-slate-500 text-[11px]">{{ $report->department->name }}</p>
                @endif
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-1.5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Problem Description</h2>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                {{ $report->description }}
            </div>
        </div>

        <!-- Resolution Notes if Resolved -->
        @if($report->status === 'resolved')
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-emerald-950 text-sm">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Work Completed & Resolved</span>
                </div>
                @if($report->resolution_notes)
                    <p class="leading-relaxed">{{ $report->resolution_notes }}</p>
                @endif
                @if($report->resolved_at)
                    <p class="text-[11px] text-emerald-700">Resolved on {{ $report->resolved_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>
        @endif

        <!-- Photo Attachments -->
        @if($report->images->isNotEmpty())
            <div class="space-y-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Photographic Evidence ({{ $report->images->count() }})</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($report->images as $img)
                        <div wire:click="openPhotoModal('{{ $img->url }}')"
                            class="group relative aspect-video bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 cursor-pointer shadow-sm">
                            <img src="{{ $img->url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                            @if($img->is_resolution_proof)
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-bold">
                                    After Resolution Proof
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Interactive Map Location -->
        <div class="space-y-2" wire:ignore>
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Reported Map Coordinates</h2>
                <span class="font-mono text-xs text-teal-800 font-semibold">{{ number_format($report->latitude, 5) }}, {{ number_format($report->longitude, 5) }}</span>
            </div>
            <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div id="report-view-map" style="height: 220px; width: 100%;"></div>
            </div>
            <p class="text-xs text-slate-600 flex items-center gap-1.5 pt-1">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <span>{{ $report->address_text }}, {{ $report->barangay }}</span>
                @if($report->landmark)
                    <span class="text-slate-400">• Near: {{ $report->landmark }}</span>
                @endif
            </p>
        </div>

        <!-- Official Barangay Progress Updates & Communication Stream -->
        <div class="space-y-3 pt-2 border-t border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Official Staff Updates</h2>

            @if($report->publicComments->isEmpty())
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                    No public updates posted yet. Barangay staff will post status notices here as work progresses.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($report->publicComments as $comment)
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $comment->user->name }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-teal-100 text-teal-800">
                                        {{ ucfirst($comment->user->role) }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">{{ $comment->comment }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- Lightbox Photo Modal -->
    @if($selectedPhotoUrl)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4" wire:click="closePhotoModal">
            <div class="relative max-w-3xl max-h-[90vh]">
                <img src="{{ $selectedPhotoUrl }}" class="rounded-2xl max-w-full max-h-[85vh] object-contain shadow-2xl">
                <button type="button" wire:click="closePhotoModal" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-900/80 text-white flex items-center justify-center font-bold text-sm">
                    ×
                </button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initViewMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initViewMap();
    });

    function initViewMap() {
        const container = document.getElementById('report-view-map');
        if (!container) return;

        const lat = {{ $report->latitude }};
        const lng = {{ $report->longitude }};

        const map = L.map('report-view-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const customPin = L.divIcon({
            className: 'custom-map-pin',
            html: '<div style="background-color:#0d9488; width:24px; height:24px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 2px 8px rgba(0,0,0,0.4);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        L.marker([lat, lng], { icon: customPin })
            .addTo(map)
            .bindPopup("<b>{{ addslashes($report->title) }}</b><br>{{ addslashes($report->address_text) }}")
            .openPopup();
    }
</script>
@endpush
