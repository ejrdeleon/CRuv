<div class="max-w-2xl mx-auto space-y-6">

    @if ($submittedReport)
        <!-- Confirmation Success Screen -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-md text-center space-y-5 animate-in fade-in duration-300">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <div class="space-y-1">
                <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 rounded-full text-xs font-bold border border-emerald-200">
                    Report Successfully Logged
                </span>
                <h1 class="text-2xl font-black text-slate-900">Maraming Salamat!</h1>
                <p class="text-xs text-slate-600 max-w-md mx-auto">
                    Your damage report has been registered in the Barangay incident management system and dispatched for verification.
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-left space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Reference Number</span>
                    <span class="text-sm font-mono font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">{{ $submittedReport->reference_number }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5 text-xs">
                    <span class="text-slate-500 font-medium">Problem Title</span>
                    <span class="font-bold text-slate-800 truncate max-w-[200px]">{{ $submittedReport->title }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5 text-xs">
                    <span class="text-slate-500 font-medium">Category</span>
                    <span class="font-semibold text-slate-700">{{ $submittedReport->category->name }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Location</span>
                    <span class="font-medium text-slate-700 truncate max-w-[200px]">{{ $submittedReport->address_text }}</span>
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 text-left flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>You can track progress, staff notes, and resolution photos at any time in <strong>My Reports</strong>.</span>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                <a href="{{ route('resident.reports.show', $submittedReport->id) }}" class="flex-1 py-3 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-sm transition text-center">
                    View Report Timeline
                </a>
                <a href="{{ route('resident.home') }}" class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition text-center">
                    Return to Home
                </a>
            </div>
        </div>
    @else
        <!-- Report Submission Form -->
        <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200 shadow-sm space-y-6">

            <!-- Title & Back -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('resident.home') }}" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900">Submit a Damage Report</h1>
                        <p class="text-xs text-slate-500">Provide details so our response team can inspect and act quickly.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-200">
                    Step 1 of 2
                </span>
            </div>

            <!-- Form Validation Errors Summary -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Please check the following required fields:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 pt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit="openConfirmModal" class="space-y-6">

                <!-- 1. Category Selection -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        1. Damage Category <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="category_id"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 font-medium bg-white">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} {{ $cat->is_hazardous ? '(CRITICAL HAZARD)' : '' }}</option>
                        @endforeach
                    </select>

                    @if ($selectedCategory && $selectedCategory->is_hazardous)
                        <!-- HAZARD ALERT BANNER -->
                        <div class="mt-3 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 space-y-2 animate-in fade-in">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">!</span>
                                <h3 class="font-bold text-sm text-rose-950">CRITICAL SAFETY ADVISORY</h3>
                            </div>
                            <p class="text-xs text-rose-800 leading-relaxed font-medium">
                                {{ $selectedCategory->safety_advisory }}
                            </p>
                            <label class="flex items-start gap-2.5 pt-2 cursor-pointer border-t border-rose-200">
                                <input type="checkbox" wire:model.live="is_hazard_warning_acknowledged"
                                    class="mt-0.5 rounded text-rose-600 focus:ring-rose-500 border-rose-300">
                                <span class="text-xs font-bold text-rose-950">
                                    I acknowledge that I am keeping a safe distance (minimum 10 meters) and will not touch or approach exposed cables or hazardous debris.
                                </span>
                            </label>
                            @error('is_hazard_warning_acknowledged')
                                <p class="text-xs text-rose-700 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <!-- 2. Title & Priority -->
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            2. Incident Title / Summary <span class="text-rose-500">*</span>
                        </label>
                        <input id="title" type="text" wire:model="title"
                            placeholder="e.g. Deep pothole near chapel / Broken streetlight post"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                        @error('title') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                                Perceived Priority / Severity
                            </label>
                            <select wire:model="priority"
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 font-medium">
                                <option value="low">Low - Minor nuisance, no immediate obstruction</option>
                                <option value="medium">Medium - Moderate disruption, needs repair</option>
                                <option value="high">High - Serious traffic risk or flood block</option>
                                <option value="urgent">Urgent - Life safety risk or exposed power line</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                                Barangay
                            </label>
                            <input type="text" wire:model="barangay"
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 bg-slate-50 font-medium" readonly>
                        </div>
                    </div>
                </div>

                <!-- 3. Detailed Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        3. Detailed Description <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="description" wire:model="description" rows="3"
                        placeholder="Describe the exact problem, size, potential risks, and any vehicles/pedestrians affected..."
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Please provide enough detail so field personnel can bring appropriate tools.</p>
                    @error('description') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- 4. Location & Interactive Map Picker -->
                <div class="space-y-3" wire:ignore>
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            4. Incident Location Pin & GPS <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" id="btn-gps" onclick="acquireGpsLocation()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-50 text-teal-800 border border-teal-200 hover:bg-teal-100 text-xs font-semibold transition active:scale-95">
                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span id="gps-status-text">Use My Current GPS</span>
                        </button>
                    </div>

                    <!-- Map Container -->
                    <div class="rounded-2xl border border-slate-300 overflow-hidden shadow-sm relative">
                        <div id="report-picker-map" style="height: 260px; width: 100%; z-index: 10;"></div>
                        <div class="absolute bottom-2 left-2 right-2 z-20 pointer-events-none">
                            <div class="bg-white/90 backdrop-blur px-3 py-1.5 rounded-lg border border-slate-200 text-[11px] text-slate-700 shadow-sm flex items-center justify-between">
                                <span>Drag the red pin or tap anywhere on the map to position.</span>
                                <span class="font-mono font-bold text-teal-800" id="coords-display">
                                    {{ number_format($latitude, 5) }}, {{ number_format($longitude, 5) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Address and Landmark -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="address_text" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            Street Address / Purok / Sitio <span class="text-rose-500">*</span>
                        </label>
                        <input id="address_text" type="text" wire:model="address_text"
                            placeholder="e.g. Purok 3, J.P. Rizal St."
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                        @error('address_text') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="landmark" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            Nearby Landmark (Optional)
                        </label>
                        <input id="landmark" type="text" wire:model="landmark"
                            placeholder="e.g. Near elementary gate, beside Sari-Sari store"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    </div>
                </div>

                <!-- 5. Photographic Attachments -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        5. Photo Evidence (Camera / Photo Gallery)
                    </label>

                    <div class="flex items-center gap-3">
                        <label class="flex-1 flex flex-col items-center justify-center p-5 border-2 border-dashed border-slate-300 hover:border-teal-500 rounded-2xl cursor-pointer bg-slate-50/50 hover:bg-teal-50/20 transition">
                            <svg class="w-8 h-8 text-teal-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-bold text-slate-700">Take Photo or Upload Files</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, WebP up to 10MB each</span>
                            <input type="file" wire:model="photos" multiple accept="image/*" class="hidden">
                        </label>
                    </div>

                    <!-- Livewire Uploading Indicator -->
                    <div wire:loading wire:target="photos" class="text-xs text-teal-700 font-semibold flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Processing and uploading photos...</span>
                    </div>

                    <!-- Photo Previews Grid -->
                    @if (!empty($photos))
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 pt-2">
                            @foreach ($photos as $index => $photo)
                                <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-square shadow-sm">
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                                    <button type="button" wire:click="removePhoto({{ $index }})"
                                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs shadow hover:bg-rose-700 transition">
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100">
                    <button type="submit"
                        class="w-full py-3.5 px-6 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-md shadow-teal-600/20 active:scale-98 transition flex items-center justify-center gap-2">
                        <span>Review & Confirm Report</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Confirmation Modal with Preview -->
    @if ($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Confirm Report Submission</h3>
                    <button type="button" wire:click="$set('showConfirmModal', false)" class="text-slate-400 hover:text-slate-700 text-xl font-bold">×</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1.5 border border-slate-200">
                        <p class="font-bold text-sm text-slate-900">{{ $title }}</p>
                        <p class="text-slate-600">{{ $description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-slate-600">
                        <div class="p-2.5 bg-slate-50 rounded-lg">
                            <span class="block text-[10px] font-semibold uppercase text-slate-400">Category</span>
                            <span class="font-bold text-slate-800">{{ $selectedCategory->name ?? 'Community Hazard' }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-lg">
                            <span class="block text-[10px] font-semibold uppercase text-slate-400">Priority</span>
                            <span class="font-bold text-teal-800 uppercase">{{ $priority }}</span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-lg space-y-1">
                        <span class="block text-[10px] font-semibold uppercase text-slate-400">Location Summary</span>
                        <p class="font-semibold text-slate-800">{{ $address_text }}, {{ $barangay }}, {{ $city }}</p>
                        @if($landmark)
                            <p class="text-slate-500">Landmark: {{ $landmark }}</p>
                        @endif
                        <p class="font-mono text-[11px] text-teal-700">GPS: {{ $latitude }}, {{ $longitude }}</p>
                    </div>

                    @if(!empty($photos))
                        <div>
                            <span class="block text-[10px] font-semibold uppercase text-slate-400 mb-1">Attached Photos ({{ count($photos) }})</span>
                            <div class="flex gap-2 overflow-x-auto pb-1">
                                @foreach($photos as $p)
                                    <img src="{{ $p->temporaryUrl() }}" class="w-14 h-14 rounded-lg object-cover border border-slate-200">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex gap-2">
                    <button type="button" wire:click="$set('showConfirmModal', false)"
                        class="flex-1 py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                        Back to Edit
                    </button>
                    <button type="button" wire:click="submit" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="submit">Confirm & Submit</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    let pickerMap = null;
    let pickerMarker = null;

    document.addEventListener('DOMContentLoaded', function() {
        initPickerMap();
    });

    document.addEventListener('livewire:navigated', function() {
        initPickerMap();
    });

    function initPickerMap() {
        const mapContainer = document.getElementById('report-picker-map');
        if (!mapContainer || pickerMap) return;

        const defaultLat = @json($latitude) || 14.1815;
        const defaultLng = @json($longitude) || 121.2845;

        pickerMap = L.map('report-picker-map').setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(pickerMap);

        const customPin = L.divIcon({
            className: 'custom-map-pin',
            html: '<div style="background-color:#0d9488; width:22px; height:22px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 2px 6px rgba(0,0,0,0.35);"></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });

        pickerMarker = L.marker([defaultLat, defaultLng], {
            draggable: true,
            icon: customPin
        }).addTo(pickerMap);

        pickerMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            updatePinPosition(pos.lat, pos.lng);
        });

        pickerMap.on('click', function(e) {
            pickerMarker.setLatLng(e.latlng);
            updatePinPosition(e.latlng.lat, e.latlng.lng);
        });
    }

    function updatePinPosition(lat, lng) {
        document.getElementById('coords-display').innerText = lat.toFixed(5) + ', ' + lng.toFixed(5);
        if (window.Livewire) {
            const comp = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
            if (comp) {
                comp.call('setCoordinates', lat, lng);
            }
        }
    }

    function acquireGpsLocation() {
        const statusSpan = document.getElementById('gps-status-text');
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        statusSpan.innerText = 'Acquiring GPS...';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                statusSpan.innerText = 'GPS Acquired!';

                if (pickerMap && pickerMarker) {
                    pickerMap.setView([lat, lng], 17);
                    pickerMarker.setLatLng([lat, lng]);
                }
                updatePinPosition(lat, lng);

                setTimeout(() => {
                    statusSpan.innerText = 'Use Current GPS';
                }, 3000);
            },
            function(error) {
                let msg = 'Unable to retrieve location.';
                if (error.code === error.PERMISSION_DENIED) {
                    msg = 'GPS permission was denied. You can manually drag the map pin to your location.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    msg = 'Location information is unavailable. Please drag the pin on the map.';
                } else if (error.code === error.TIMEOUT) {
                    msg = 'GPS request timed out. Please drag the pin on the map.';
                }
                alert(msg);
                statusSpan.innerText = 'Use Current GPS';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
</script>
@endpush
