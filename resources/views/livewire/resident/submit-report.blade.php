<div class="max-w-2xl mx-auto space-y-4">

    @if ($submittedReport)
        <!-- Confirmation Success Screen -->
        <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900">Incident Report Logged</h1>
                    <p class="text-xs text-slate-500">Your report has been received and queued for barangay verification.</p>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-md p-3.5 space-y-2.5 text-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="font-medium text-slate-500">Tracking Reference</span>
                    <span class="font-mono font-bold text-teal-800 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $submittedReport->reference_number }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="font-medium text-slate-500">Title</span>
                    <span class="font-semibold text-slate-900 truncate max-w-[220px]">{{ $submittedReport->title }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="font-medium text-slate-500">Category</span>
                    <span class="font-medium text-slate-800">{{ $submittedReport->category->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-medium text-slate-500">Location</span>
                    <span class="font-medium text-slate-800 truncate max-w-[220px]">{{ $submittedReport->address_text }}</span>
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-md border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
                <span class="font-bold shrink-0">Note:</span>
                <span>Field response personnel are assigned based on hazard severity and SLA windows. You can follow real-time status in <strong>My Reports</strong>.</span>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('resident.reports.show', $submittedReport->id) }}" class="flex-1 py-2 px-4 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition text-center">
                    View Report Dossier
                </a>
                <a href="{{ route('resident.home') }}" class="py-2 px-4 rounded-md bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 transition text-center">
                    Return to Portal
                </a>
            </div>
        </div>
    @else
        <!-- Report Submission Form -->
        <div class="bg-white rounded-lg p-5 sm:p-6 border border-slate-200 shadow-xs space-y-5">

            <!-- Title & Back -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('resident.home') }}" class="p-1 rounded-md text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition" aria-label="Back">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900">Submit Damage or Hazard Report</h1>
                        <p class="text-[11px] text-slate-500">Accurate location and photos speed up inspection and dispatch.</p>
                    </div>
                </div>
            </div>

            <!-- Form Validation Errors Summary -->
            @if ($errors->any())
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-md text-xs space-y-1">
                    <p class="font-bold">Please correct the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5 pt-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit="openConfirmModal" class="space-y-4">

                <!-- 1. Category Selection -->
                <div class="space-y-1.5">
                    <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        1. Damage Category <span class="text-rose-600">*</span>
                    </label>
                    <select id="category_id" wire:model.live="category_id"
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 font-medium bg-white">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} {{ $cat->is_hazardous ? '(CRITICAL HAZARD)' : '' }}</option>
                        @endforeach
                    </select>

                    @if ($selectedCategory && $selectedCategory->is_hazardous)
                        <!-- HAZARD ALERT BANNER -->
                        <div class="mt-2.5 p-3 rounded-md bg-rose-50 border border-rose-200 text-rose-900 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded bg-rose-700 text-white flex items-center justify-center font-bold text-xs shrink-0">!</span>
                                <h3 class="font-bold text-xs text-rose-950 uppercase tracking-wide">Safety Advisory: {{ $selectedCategory->name }}</h3>
                            </div>
                            <p class="text-xs text-rose-800 leading-relaxed">
                                {{ $selectedCategory->safety_advisory }}
                            </p>
                            <label class="flex items-start gap-2 pt-2 cursor-pointer border-t border-rose-200">
                                <input type="checkbox" wire:model.live="is_hazard_warning_acknowledged"
                                    class="mt-0.5 rounded text-rose-700 focus:ring-rose-500 border-rose-300">
                                <span class="text-xs font-semibold text-rose-950">
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
                <div class="space-y-3">
                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            2. Incident Title / Summary <span class="text-rose-600">*</span>
                        </label>
                        <input id="title" type="text" wire:model="title"
                            placeholder="e.g. Deep pothole near chapel / Broken streetlight post"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        @error('title') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="priority" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                                Perceived Priority
                            </label>
                            <select id="priority" wire:model="priority"
                                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 bg-white">
                                <option value="low">Low - Minor nuisance, no disruption</option>
                                <option value="medium">Medium - Moderate disruption</option>
                                <option value="high">High - Serious traffic risk or flood block</option>
                                <option value="urgent">Urgent - Life safety risk or live wire</option>
                            </select>
                        </div>

                        <div>
                            <label for="barangay" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                                Barangay
                            </label>
                            <input id="barangay" type="text" wire:model="barangay"
                                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-slate-50 font-medium" readonly>
                        </div>
                    </div>
                </div>

                <!-- 3. Detailed Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        3. Detailed Description <span class="text-rose-600">*</span>
                    </label>
                    <textarea id="description" wire:model="description" rows="3"
                        placeholder="Describe the problem, dimensions, safety risks, and any affected facilities..."
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Include useful observations for field responders.</p>
                    @error('description') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- 4. Location & Interactive Map Picker -->
                <div class="space-y-2" wire:ignore>
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            4. Map Pin Location <span class="text-rose-600">*</span>
                        </label>
                        <button type="button" id="btn-gps" onclick="acquireGpsLocation()"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-[11px] font-semibold transition">
                            <svg class="w-3.5 h-3.5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span id="gps-status-text">Acquire GPS</span>
                        </button>
                    </div>

                    <!-- Map Container -->
                    <div class="rounded-md border border-slate-300 overflow-hidden relative">
                        <div id="report-picker-map" style="height: 240px; width: 100%; z-index: 10;"></div>
                        <div class="absolute bottom-2 left-2 right-2 z-20 pointer-events-none">
                            <div class="bg-white/95 px-2.5 py-1 rounded border border-slate-200 text-[11px] text-slate-700 shadow-xs flex items-center justify-between">
                                <span>Tap map or drag pin to position</span>
                                <span class="font-mono font-semibold text-teal-800" id="coords-display">
                                    {{ number_format($latitude, 5) }}, {{ number_format($longitude, 5) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Address and Landmark -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="address_text" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            Street Address / Purok <span class="text-rose-600">*</span>
                        </label>
                        <input id="address_text" type="text" wire:model="address_text"
                            placeholder="e.g. Purok 3, J.P. Rizal St."
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        @error('address_text') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="landmark" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            Nearby Landmark (Optional)
                        </label>
                        <input id="landmark" type="text" wire:model="landmark"
                            placeholder="e.g. Near chapel, beside basketball court"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    </div>
                </div>

                <!-- 5. Photographic Attachments -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        5. Photo Evidence (Optional)
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border border-dashed border-slate-300 hover:border-slate-400 rounded-md cursor-pointer bg-slate-50 hover:bg-slate-100/70 transition">
                        <svg class="w-6 h-6 text-slate-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-xs font-semibold text-slate-700">Select photos or take a picture</span>
                        <span class="text-[10px] text-slate-500 mt-0.5">JPG, PNG, WebP up to 10MB each</span>
                        <input type="file" wire:model="photos" multiple accept="image/*" class="hidden">
                    </label>

                    <!-- Livewire Uploading Indicator -->
                    <div wire:loading wire:target="photos" class="text-xs text-teal-800 font-medium flex items-center gap-1.5">
                        <svg class="animate-spin w-3.5 h-3.5 text-teal-800" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Uploading photos...</span>
                    </div>

                    <!-- Photo Previews Grid -->
                    @if (!empty($photos))
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1">
                            @foreach ($photos as $index => $photo)
                                <div class="relative group rounded-md overflow-hidden border border-slate-200 bg-slate-100 aspect-square">
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                                    <button type="button" wire:click="removePhoto({{ $index }})"
                                        class="absolute top-1 right-1 w-5 h-5 rounded bg-slate-900/80 text-white flex items-center justify-center text-xs hover:bg-rose-700 transition" title="Remove photo">
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-t border-slate-100">
                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition flex items-center justify-center gap-1.5">
                        <span>Review & Submit Report</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Confirmation Modal with Preview -->
    @if ($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-lg w-full p-5 shadow-lg border border-slate-200 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900">Confirm Report Details</h3>
                    <button type="button" wire:click="$set('showConfirmModal', false)" class="text-slate-400 hover:text-slate-700 text-lg leading-none">×</button>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-md border border-slate-200 space-y-1">
                        <p class="font-bold text-slate-900">{{ $title }}</p>
                        <p class="text-slate-600 leading-relaxed">{{ $description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2 bg-slate-50 rounded border border-slate-200">
                            <span class="block text-[10px] font-semibold uppercase text-slate-500">Category</span>
                            <span class="font-semibold text-slate-800">{{ $selectedCategory->name ?? 'Hazard' }}</span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded border border-slate-200">
                            <span class="block text-[10px] font-semibold uppercase text-slate-500">Priority</span>
                            <span class="font-semibold text-slate-800 uppercase">{{ $priority }}</span>
                        </div>
                    </div>

                    <div class="p-2 bg-slate-50 rounded border border-slate-200 space-y-0.5">
                        <span class="block text-[10px] font-semibold uppercase text-slate-500">Location</span>
                        <p class="font-semibold text-slate-800">{{ $address_text }}, {{ $barangay }}, {{ $city }}</p>
                        @if($landmark)
                            <p class="text-slate-500">Landmark: {{ $landmark }}</p>
                        @endif
                        <p class="font-mono text-[11px] text-slate-600">Coordinates: {{ $latitude }}, {{ $longitude }}</p>
                    </div>

                    @if(!empty($photos))
                        <div>
                            <span class="block text-[10px] font-semibold uppercase text-slate-500 mb-1">Attached Photos ({{ count($photos) }})</span>
                            <div class="flex gap-1.5 overflow-x-auto pb-1">
                                @foreach($photos as $p)
                                    <img src="{{ $p->temporaryUrl() }}" class="w-12 h-12 rounded object-cover border border-slate-200">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-200 flex gap-2">
                    <button type="button" wire:click="$set('showConfirmModal', false)"
                        class="flex-1 py-2 px-3 rounded-md border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                        Edit Information
                    </button>
                    <button type="button" wire:click="submit" wire:loading.attr="disabled"
                        class="flex-1 py-2 px-3 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="submit">Submit Report</span>
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

        const defaultLat = @json($latitude) || 14.2816;
        const defaultLng = @json($longitude) || 121.4146;

        pickerMap = L.map('report-picker-map').setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(pickerMap);

        const customPin = L.divIcon({
            className: 'custom-map-pin',
            html: '<div style="background-color:#0f766e; width:20px; height:20px; border-radius:50%; border:2.5px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
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
        const coordsEl = document.getElementById('coords-display');
        if (coordsEl) {
            coordsEl.innerText = lat.toFixed(5) + ', ' + lng.toFixed(5);
        }
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
                statusSpan.innerText = 'GPS Acquired';

                if (pickerMap && pickerMarker) {
                    pickerMap.setView([lat, lng], 17);
                    pickerMarker.setLatLng([lat, lng]);
                }
                updatePinPosition(lat, lng);

                setTimeout(() => {
                    statusSpan.innerText = 'Acquire GPS';
                }, 3000);
            },
            function(error) {
                let msg = 'Unable to retrieve location.';
                if (error.code === error.PERMISSION_DENIED) {
                    msg = 'GPS permission was denied. You can manually drag the pin on the map.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    msg = 'Location information is unavailable. Please drag the pin on the map.';
                } else if (error.code === error.TIMEOUT) {
                    msg = 'GPS request timed out. Please drag the pin on the map.';
                }
                alert(msg);
                statusSpan.innerText = 'Acquire GPS';
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
