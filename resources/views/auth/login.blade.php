<x-layouts.app title="Sign In - Barangay Damage Reporting">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-600 text-white mx-auto flex items-center justify-center font-bold text-2xl shadow-md ring-4 ring-teal-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">{{ config('community.full_location', 'Santa Cruz, Laguna, Philippines') }}</h2>
            <p class="text-sm text-slate-600 mt-1">Community Damage Reporting & Monitoring System</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-2xl sm:px-10">

                <!-- Quick Demo Logins for Pair Programming / Review -->
                <div class="mb-6 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Quick Demo Accounts</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" onclick="fillDemo('admin@barangay.gov.ph', 'password123')" class="px-3 py-2 text-xs font-bold rounded-lg bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition text-center flex items-center justify-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                            <span>Barangay Admin</span>
                        </button>
                        <button type="button" onclick="fillDemo('maria.santos@gmail.com', 'password123')" class="px-3 py-2 text-xs font-bold rounded-lg bg-teal-50 text-teal-700 border border-teal-200 hover:bg-teal-100 transition text-center flex items-center justify-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                            <span>Resident User</span>
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase">Email Address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="you@example.com">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-teal-600 focus:ring-teal-500 border-slate-300">
                            <span class="text-slate-600">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 border-t border-slate-200 pt-4 text-center">
                    <p class="text-xs text-slate-600">
                        New community resident?
                        <a href="{{ route('register') }}" class="font-semibold text-teal-700 hover:text-teal-800">
                            Register an account
                        </a>
                    </p>
                </div>
            </div>

            <!-- Emergency Utility Notice banner -->
            <div class="mt-6 p-4 rounded-xl bg-amber-50 border border-amber-200/80 text-xs text-amber-900 space-y-1">
                <p class="font-semibold flex items-center gap-1.5 text-amber-950">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Immediate Danger Notice
                </p>
                <p>For fallen live electric cables, fires, or medical emergencies, please stay away and call emergency dispatch <strong>911</strong> or Meralco <strong>16211</strong> immediately.</p>
            </div>
        </div>
    </div>

    <script>
        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</x-layouts.app>
