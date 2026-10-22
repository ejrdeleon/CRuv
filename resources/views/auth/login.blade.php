<x-layouts.app title="Admin Sign In - {{ config('community.municipality', 'Santa Cruz') }}, {{ config('community.province', 'Laguna') }}">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-100 dark:bg-slate-950">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4">
            <div class="w-12 h-12 rounded-lg bg-teal-700 text-white mx-auto flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="mt-3 text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ config('community.full_location', 'Santa Cruz, Laguna, Philippines') }}</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Municipal Admin & Operations Command Portal</p>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white dark:bg-slate-900 py-7 px-6 border border-slate-200 dark:border-slate-800 rounded-lg sm:px-8">

                <!-- Quick Demo Login for Admin Review -->
                <div class="mb-5 p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-md flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Demo Administrator</span>
                    <button type="button" onclick="fillDemo('admin@barangay.gov.ph', 'password123')" class="px-2.5 py-1 text-xs font-semibold rounded bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800 hover:bg-teal-100 dark:hover:bg-teal-900 transition">
                        Fill Admin Credentials
                    </button>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-md text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Administrator Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="mt-1 block w-full rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600"
                            placeholder="admin@barangay.gov.ph">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="mt-1 block w-full rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600"
                            placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-teal-700 focus:ring-teal-600 border-slate-300 dark:border-slate-700">
                            <span class="text-slate-600 dark:text-slate-400">Keep session active</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 rounded-md text-xs font-semibold text-white bg-teal-700 hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600 transition">
                        Sign In to Operations Portal
                    </button>
                </form>

                <div class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4 text-center">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Authorized Municipal Personnel Only. Residents use the mobile app to submit and track community reports.
                    </p>
                </div>
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
