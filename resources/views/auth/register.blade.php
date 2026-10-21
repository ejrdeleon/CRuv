<x-layouts.app title="Resident Registration - Barangay San Isidro">
    <div class="min-h-screen flex flex-col justify-center py-8 sm:px-6 lg:px-8 bg-slate-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4">
            <div class="w-9 h-9 rounded-md bg-teal-800 text-white mx-auto flex items-center justify-center font-bold text-sm shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="mt-2.5 text-lg font-bold tracking-tight text-slate-900">Resident Account Registration</h1>
            <p class="text-xs text-slate-500 mt-0.5">Barangay San Isidro Incident and Hazard Reporting System</p>
        </div>

        <div class="mt-4 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white py-6 px-5 sm:px-7 shadow-xs border border-slate-200 rounded-lg">

                @if ($errors->any())
                    <div class="mb-3.5 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-md text-xs space-y-0.5">
                        <p class="font-bold">Registration error:</p>
                        @foreach ($errors->all() as $error)
                            <p>• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label for="name" class="block font-semibold text-slate-700 uppercase text-[11px]">Full Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"
                            placeholder="e.g. Maria Clara Santos">
                    </div>

                    <div>
                        <label for="email" class="block font-semibold text-slate-700 uppercase text-[11px]">Email Address</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"
                            placeholder="maria@example.com">
                    </div>

                    <div>
                        <label for="phone" class="block font-semibold text-slate-700 uppercase text-[11px]">Mobile Number</label>
                        <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"
                            placeholder="0917-000-0000">
                    </div>

                    <div>
                        <label for="barangay" class="block font-semibold text-slate-700 uppercase text-[11px]">Barangay</label>
                        <select id="barangay" name="barangay" required
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 bg-white">
                            <option value="Barangay San Isidro" {{ old('barangay') == 'Barangay San Isidro' ? 'selected' : '' }}>Barangay San Isidro</option>
                            <option value="Barangay Poblacion" {{ old('barangay') == 'Barangay Poblacion' ? 'selected' : '' }}>Barangay Poblacion</option>
                            <option value="Barangay San Nicolas" {{ old('barangay') == 'Barangay San Nicolas' ? 'selected' : '' }}>Barangay San Nicolas</option>
                            <option value="Barangay Maitim" {{ old('barangay') == 'Barangay Maitim' ? 'selected' : '' }}>Barangay Maitim</option>
                            <option value="Barangay Calo" {{ old('barangay') == 'Barangay Calo' ? 'selected' : '' }}>Barangay Calo</option>
                        </select>
                    </div>

                    <div>
                        <label for="address" class="block font-semibold text-slate-700 uppercase text-[11px]">Street / Purok / Subdivision (Optional)</label>
                        <input id="address" name="address" type="text" value="{{ old('address') }}"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"
                            placeholder="e.g. Purok 3, Rizal Avenue">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label for="password" class="block font-semibold text-slate-700 uppercase text-[11px]">Password</label>
                            <input id="password" name="password" type="password" required
                                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"
                                placeholder="Min 8 characters">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block font-semibold text-slate-700 uppercase text-[11px]">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700"
                                placeholder="Re-enter password">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center py-2 px-4 rounded-md shadow-xs text-xs font-semibold text-white bg-teal-800 hover:bg-teal-900 transition">
                            Register Account
                        </button>
                    </div>
                </form>

                <div class="mt-4 border-t border-slate-200 pt-3 text-center">
                    <p class="text-xs text-slate-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-semibold text-teal-800 hover:underline">
                            Log in
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
