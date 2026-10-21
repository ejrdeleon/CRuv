<x-layouts.app title="Resident Registration - Barangay Damage Reporting">
    <div class="min-h-screen flex flex-col justify-center py-10 sm:px-6 lg:px-8 bg-slate-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white mx-auto flex items-center justify-center font-bold text-xl shadow-md">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Resident Registration</h2>
            <p class="text-xs text-slate-600 mt-1">Register to report road damage, fallen lines, and track repair updates</p>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-2xl sm:px-10">

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase">Full Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="e.g. Maria Clara Santos">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase">Email Address</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="maria@example.com">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase">Mobile Number</label>
                        <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="0917-000-0000">
                    </div>

                    <div>
                        <label for="barangay" class="block text-xs font-semibold text-slate-700 uppercase">Barangay</label>
                        <select id="barangay" name="barangay" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                            <option value="Barangay San Isidro" {{ old('barangay') == 'Barangay San Isidro' ? 'selected' : '' }}>Barangay San Isidro (Current Jurisdiction)</option>
                            <option value="Barangay Poblacion" {{ old('barangay') == 'Barangay Poblacion' ? 'selected' : '' }}>Barangay Poblacion</option>
                            <option value="Barangay San Nicolas" {{ old('barangay') == 'Barangay San Nicolas' ? 'selected' : '' }}>Barangay San Nicolas</option>
                            <option value="Barangay Maitim" {{ old('barangay') == 'Barangay Maitim' ? 'selected' : '' }}>Barangay Maitim</option>
                            <option value="Barangay Calo" {{ old('barangay') == 'Barangay Calo' ? 'selected' : '' }}>Barangay Calo</option>
                        </select>
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-semibold text-slate-700 uppercase">Street / Purok / Subdivision (Optional)</label>
                        <input id="address" name="address" type="text" value="{{ old('address') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="e.g. Purok 3, Rizal Avenue">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase">Password</label>
                        <input id="password" name="password" type="password" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Minimum 8 characters">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Re-enter password">
                    </div>

                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition">
                        Complete Registration
                    </button>
                </form>

                <div class="mt-6 border-t border-slate-200 pt-4 text-center">
                    <p class="text-xs text-slate-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-semibold text-teal-700 hover:text-teal-800">
                            Sign in here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
