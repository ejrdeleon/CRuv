<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('admin.reports') }}" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <span class="font-mono text-xs font-black text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                    {{ $report->reference_number }}
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">
                    Submitted {{ $report->created_at->format('M d, Y h:i A') }}
                </span>
            </div>
            <h1 class="text-lg font-bold text-slate-900">{{ $report->title }}</h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Priority selector -->
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-xl text-xs">
                <span class="text-slate-500 font-semibold uppercase text-[10px]">Priority:</span>
                <select wire:model="selectedPriority" wire:change="updatePriority"
                    class="bg-transparent font-bold uppercase focus:outline-none text-slate-800">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>

            <!-- Assign Button -->
            <button type="button" wire:click="openAssignModal"
                class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>{{ $report->department_id ? 'Reassign Department' : 'Assign Department' }}</span>
            </button>

            <!-- Status Transition Button -->
            <button type="button" wire:click="openStatusModal"
                class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Update Status</span>
            </button>
        </div>
    </div>

    <!-- Main Content Layout (2 columns on desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details, Map, Photos, Discussion -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Category Safety Advisory Alert if Hazardous -->
            @if($report->category->is_hazardous)
                <div class="p-4 rounded-3xl bg-rose-50 border border-rose-300 text-rose-900 space-y-1.5 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs">!</span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-rose-950">Electrical / Critical Hazard Case</h3>
                    </div>
                    <p class="text-xs text-rose-800 leading-relaxed font-medium">
                        {{ $report->category->safety_advisory }}
                    </p>
                    <p class="text-[11px] text-rose-700 font-semibold pt-1">
                        Reminder: This entry is a coordination log. For exposed live power lines, verify that tanods have cordoned off the area and notify the power utility hotline.
                    </p>
                </div>
            @endif

            <!-- Report Overview Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Incident Description</h2>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                        {{ $report->status_label }}
                    </span>
                </div>

                <div class="text-xs text-slate-800 leading-relaxed whitespace-pre-line font-normal">
                    {{ $report->description }}
                </div>

                @if($report->status === 'resolved' && $report->resolution_notes)
                    <div class="mt-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1.5">
                        <p class="font-bold flex items-center gap-1.5 text-emerald-950">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Resolution Summary
                        </p>
                        <p>{{ $report->resolution_notes }}</p>
                        @if($report->resolved_at)
                            <span class="text-[10px] text-emerald-700 block">Resolved at {{ $report->resolved_at->format('M d, Y h:i A') }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Photos Card -->
            @if($report->images->isNotEmpty())
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Report Photographs ({{ $report->images->count() }})</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($report->images as $img)
                            <div class="relative aspect-video rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 group shadow-sm">
                                <img src="{{ $img->url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                <div class="absolute bottom-0 inset-x-0 p-2 bg-gradient-to-t from-slate-950/80 to-transparent text-[10px] text-white">
                                    {{ $img->is_resolution_proof ? '✓ Resolution Proof' : ($img->caption ?? 'Attachment') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Location Map Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-3" wire:ignore>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Incident Coordinates & Map</h2>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5">{{ $report->address_text }}, {{ $report->barangay }}</p>
                    </div>
                    <span class="font-mono text-xs text-teal-800 font-bold bg-teal-50 px-2 py-1 rounded-lg">
                        {{ number_format($report->latitude, 5) }}, {{ number_format($report->longitude, 5) }}
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-inner">
                    <div id="admin-detail-map" style="height: 260px; width: 100%;"></div>
                </div>

                @if($report->landmark)
                    <p class="text-xs text-slate-500">
                        <strong>Nearby Landmark:</strong> {{ $report->landmark }}
                    </p>
                @endif
            </div>

            <!-- Notes & Official Updates Stream -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Staff Notes & Public Updates</h2>
                    <span class="text-xs text-slate-400">{{ $report->comments->count() }} messages</span>
                </div>

                <!-- Add Comment Form -->
                <form wire:submit="addComment" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div>
                        <label for="newComment" class="block text-xs font-bold text-slate-700 uppercase mb-1">Add Note / Progress Update</label>
                        <textarea id="newComment" wire:model="newComment" rows="2"
                            placeholder="Add action report, dispatch details, or public notice to resident..."
                            class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"></textarea>
                        @error('newComment') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer text-xs">
                            <input type="checkbox" wire:model="isInternalComment" class="rounded text-teal-600 border-slate-300">
                            <span class="font-semibold text-slate-700">Internal staff note only (Hidden from resident)</span>
                        </label>

                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                            Post Note
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-3">
                    @forelse($report->comments as $comment)
                        <div class="p-3.5 rounded-2xl border text-xs space-y-1 {{ $comment->is_internal ? 'bg-amber-50/40 border-amber-200' : 'bg-slate-50 border-slate-200' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $comment->user->name }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold {{ $comment->user->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($comment->user->role) }}
                                    </span>
                                    @if($comment->is_internal)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-200 text-amber-900 uppercase">
                                            Internal Only
                                        </span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-teal-100 text-teal-800">
                                            Public to Resident
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $comment->comment }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">No notes or comments posted yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Col: Reporter Info, Assignment, Status Audit Trail -->
        <div class="space-y-6">

            <!-- Reporter Profile (Staff Only View) -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Resident / Reporter Information</h2>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Name</span>
                        <p class="font-bold text-slate-900">{{ $report->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Phone Contact</span>
                        <a href="tel:{{ $report->user->phone }}" class="font-bold text-teal-700 hover:underline">{{ $report->user->phone }}</a>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Email Address</span>
                        <p class="text-slate-600 truncate">{{ $report->user->email }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Registered Address</span>
                        <p class="text-slate-600">{{ $report->user->address ?? 'Barangay San Isidro' }}</p>
                    </div>
                </div>
            </div>

            <!-- Current Assignment Card -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Assignment Details</h2>
                    <button type="button" wire:click="openAssignModal" class="text-xs font-bold text-teal-700 hover:underline">Change</button>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="p-2.5 bg-slate-50 rounded-xl">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block">Department</span>
                        <p class="font-bold text-slate-800">{{ $report->department?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block">Officer Assigned</span>
                        <p class="font-bold text-slate-800">{{ $report->assignedStaff?->name ?? 'Unassigned Officer' }}</p>
                        @if($report->assignedStaff?->phone)
                            <p class="text-[11px] text-teal-700 font-semibold">{{ $report->assignedStaff->phone }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status History Audit Trail -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Status History Audit</h2>
                <div class="space-y-2.5 text-xs">
                    @foreach($report->statusHistories as $history)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-slate-800 uppercase tracking-tight">
                                    {{ $history->from_status ? ucfirst(str_replace('_', ' ', $history->from_status)) . ' → ' : '' }}
                                    {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                                </span>
                                <span class="text-slate-400">{{ $history->created_at->format('M d, H:i') }}</span>
                            </div>
                            @if($history->comment)
                                <p class="text-slate-600 leading-snug text-[11px]">{{ $history->comment }}</p>
                            @endif
                            <span class="text-[10px] text-slate-400 block">By: {{ $history->user?->name ?? 'System' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- Status Change Modal -->
    @if($showStatusModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Update Report Status</h3>
                    <button type="button" wire:click="$set('showStatusModal', false)" class="text-slate-400 hover:text-slate-700 text-xl font-bold">×</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Status</label>
                        <select wire:model.live="newStatus" class="w-full p-2.5 rounded-xl border border-slate-300 font-bold bg-white text-sm">
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="verified">Verified</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                            <option value="duplicate">Duplicate Case</option>
                        </select>
                    </div>

                    @if($newStatus === 'rejected')
                        <div>
                            <label class="block text-xs font-bold text-rose-700 uppercase mb-1">Reason for Rejection *</label>
                            <textarea wire:model="rejectionReason" rows="3"
                                placeholder="State clearly why this report cannot be accepted or is out of jurisdiction..."
                                class="w-full rounded-xl border border-rose-300 p-2.5 text-xs focus:ring-rose-500"></textarea>
                            @error('rejectionReason') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @elseif($newStatus === 'duplicate')
                        <div>
                            <label class="block text-xs font-bold text-amber-800 uppercase mb-1">Link to Primary Report *</label>
                            <select wire:model="duplicateOfId" class="w-full p-2.5 rounded-xl border border-amber-300 font-medium bg-white text-xs">
                                <option value="">Select primary case reference...</option>
                                @foreach($otherReports as $or)
                                    <option value="{{ $or->id }}">#{{ $or->reference_number }} - {{ $or->title }}</option>
                                @endforeach
                            </select>
                            @error('duplicateOfId') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @elseif($newStatus === 'resolved')
                        <div class="space-y-3 p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200">
                            <p class="font-bold text-emerald-950">Resolution Details & After Proof</p>
                            <div>
                                <label class="block text-xs font-semibold text-emerald-900 mb-1">Work Done / Resolution Notes *</label>
                                <textarea wire:model="resolutionNotes" rows="3"
                                    placeholder="e.g. Cleared 15 bags of debris / Replaced street lamp / Repaired pothole with asphalt compound..."
                                    class="w-full rounded-xl border border-emerald-300 p-2.5 text-xs bg-white"></textarea>
                                @error('resolutionNotes') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-emerald-900 mb-1">After-Resolution Photograph (Optional)</label>
                                <input type="file" wire:model="resolutionPhoto" accept="image/*" class="text-xs">
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Note / Audit Reason</label>
                        <input type="text" wire:model="statusComment" placeholder="e.g. Inspected site, crew dispatched"
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showStatusModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateStatus" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm">
                        Confirm Status Update
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Assign Modal -->
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Assign Report</h3>
                    <button type="button" wire:click="$set('showAssignModal', false)" class="text-slate-400 hover:text-slate-700 text-xl font-bold">×</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Responsible Department</label>
                        <select wire:model="assignedDepartmentId" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white">
                            <option value="">Select Department...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Assign Staff Officer</label>
                        <select wire:model="assignedStaffId" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white">
                            <option value="">Select Staff...</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->department?->code ?? 'Admin' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Assignment Instructions</label>
                        <textarea wire:model="assignmentNotes" rows="2" placeholder="e.g. Inspect site by 2pm and coordinate with traffic tanod"
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs"></textarea>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showAssignModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" wire:click="assignReport" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm">
                        Save Assignment
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initAdminDetailMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initAdminDetailMap();
    });

    function initAdminDetailMap() {
        const container = document.getElementById('admin-detail-map');
        if (!container) return;

        const lat = {{ $report->latitude }};
        const lng = {{ $report->longitude }};

        const map = L.map('admin-detail-map', {
            zoomControl: true
        }).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let color = '#0d9488';
        if ('{{ $report->priority }}' === 'urgent') color = '#e11d48';
        else if ('{{ $report->status }}' === 'resolved') color = '#10b981';

        const customPin = L.divIcon({
            className: 'custom-admin-marker',
            html: `<div style="background-color:${color}; width:24px; height:24px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 2px 8px rgba(0,0,0,0.4);"></div>`,
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
