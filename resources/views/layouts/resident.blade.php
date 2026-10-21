<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d9488">
    <meta name="description" content="Community Damage Reporting and Monitoring System">

    <title>{{ $title ?? 'Community Damage Reporting' }} - {{ config('app.name') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col min-h-screen pb-20 md:pb-6">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Branding -->
                <a href="{{ route('resident.home') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-slate-900 leading-tight">Barangay San Isidro</span>
                        <span class="block text-xs text-teal-700 font-medium">Community Reports</span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('resident.home') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('resident.home') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Home
                    </a>
                    <a href="{{ route('resident.reports.create') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('resident.reports.create') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Report Problem
                    </a>
                    <a href="{{ route('resident.reports.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('resident.reports.*') && !request()->routeIs('resident.reports.create') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        My Reports
                    </a>
                    <a href="{{ route('resident.notifications') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium relative {{ request()->routeIs('resident.notifications') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Notifications
                        @php $unreadCount = auth()->check() ? auth()->user()->notifications()->where('is_read', false)->count() : 0; @endphp
                        @if($unreadCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold bg-rose-600 text-white rounded-full">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </nav>

                <!-- User profile / logout -->
                <div class="flex items-center gap-3">
                    @auth
                        @php $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                        <a href="{{ route('resident.notifications') }}" class="md:hidden relative p-2 text-slate-600 hover:text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('profile') }}" class="flex items-center gap-2 py-1 px-2.5 rounded-lg hover:bg-slate-100 transition text-left">
                                <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <span class="hidden sm:inline-block text-xs font-semibold text-slate-700 max-w-[120px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" title="Log Out" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">Log In</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 px-3 py-1.5 rounded-lg">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Global Alerts -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-start gap-2.5 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-start gap-2.5 shadow-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation Bar (Resident App Shell) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 px-3 py-2 flex items-center justify-around shadow-lg">
        <a href="{{ route('resident.home') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-xs {{ request()->routeIs('resident.home') ? 'text-teal-700 font-bold' : 'text-slate-500 font-medium' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Home</span>
        </a>

        <a href="{{ route('resident.reports.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-xs {{ request()->routeIs('resident.reports.*') && !request()->routeIs('resident.reports.create') ? 'text-teal-700 font-bold' : 'text-slate-500 font-medium' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>My Reports</span>
        </a>

        <!-- Big Center CTA: Report -->
        <a href="{{ route('resident.reports.create') }}" class="flex flex-col items-center -mt-5">
            <div class="w-13 h-13 rounded-full bg-teal-600 text-white flex items-center justify-center shadow-lg shadow-teal-600/30 ring-4 ring-white active:scale-95 transition">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span class="text-[11px] font-bold text-teal-700 mt-1">Report</span>
        </a>

        <a href="{{ route('resident.notifications') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-xs relative {{ request()->routeIs('resident.notifications') ? 'text-teal-700 font-bold' : 'text-slate-500 font-medium' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @php $unreadCount = auth()->check() ? auth()->user()->notifications()->where('is_read', false)->count() : 0; @endphp
            @if($unreadCount > 0)
                <span class="absolute top-0 right-3 w-2 h-2 bg-rose-500 rounded-full"></span>
            @endif
            <span>Alerts</span>
        </a>

        <a href="{{ route('profile') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-xs {{ request()->routeIs('profile') ? 'text-teal-700 font-bold' : 'text-slate-500 font-medium' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profile</span>
        </a>
    </nav>

    @livewireScripts
    @stack('scripts')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.debug('ServiceWorker registration omitted:', err);
                });
            });
        }
    </script>
</body>
</html>
