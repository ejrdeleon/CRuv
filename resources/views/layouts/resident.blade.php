<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <meta name="description" content="{{ config('community.full_location', 'Santa Cruz, Laguna, Philippines') }} Incident Reporting System">

    <title>{{ $title ?? 'Damage Reporting' }} - {{ config('community.municipality', 'Santa Cruz') }}, {{ config('community.province', 'Laguna') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col min-h-screen pb-16 md:pb-6">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <!-- Branding -->
                <a href="{{ route('resident.home') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-md bg-teal-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <span class="block text-xs font-bold text-slate-900 tracking-tight">{{ config('community.municipality', 'Santa Cruz') }}, {{ config('community.province', 'Laguna') }}</span>
                        <span class="block text-[11px] text-slate-500">Community Incident Reporting</span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-1 text-xs font-medium text-slate-600">
                    <a href="{{ route('resident.home') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('resident.home') ? 'bg-slate-100 text-slate-900 font-semibold' : 'hover:text-slate-900 hover:bg-slate-50' }}">
                        Home
                    </a>
                    <a href="{{ route('resident.reports.create') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('resident.reports.create') ? 'bg-teal-50 text-teal-800 font-semibold border border-teal-200' : 'hover:text-slate-900 hover:bg-slate-50' }}">
                        + Report Incident
                    </a>
                    <a href="{{ route('resident.reports.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('resident.reports.*') && !request()->routeIs('resident.reports.create') ? 'bg-slate-100 text-slate-900 font-semibold' : 'hover:text-slate-900 hover:bg-slate-50' }}">
                        My Reports
                    </a>
                    <a href="{{ route('resident.notifications') }}" class="px-3 py-1.5 rounded-md relative {{ request()->routeIs('resident.notifications') ? 'bg-slate-100 text-slate-900 font-semibold' : 'hover:text-slate-900 hover:bg-slate-50' }}">
                        Notifications
                        @php $unreadCount = auth()->check() ? auth()->user()->notifications()->where('is_read', false)->count() : 0; @endphp
                        @if($unreadCount > 0)
                            <span class="ml-1 px-1.5 py-0.2 text-[10px] font-bold bg-rose-600 text-white rounded">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </nav>

                <!-- User profile / logout -->
                <div class="flex items-center gap-2">
                    @auth
                        @php $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                        <a href="{{ route('resident.notifications') }}" class="md:hidden relative p-1.5 text-slate-500 hover:text-slate-800" aria-label="Notifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 w-2 h-2 bg-rose-600 rounded-full"></span>
                            @endif
                        </a>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('profile') }}" class="flex items-center gap-2 py-1 px-2 rounded-md hover:bg-slate-100 text-left text-xs text-slate-700">
                                <span class="w-6 h-6 rounded bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-[10px]">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </span>
                                <span class="hidden sm:inline font-medium max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" title="Sign out" class="p-1.5 text-slate-400 hover:text-slate-700 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-teal-800 hover:underline">Log in</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Global Alerts -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 mt-3 w-full">
        @if (session('success'))
            <div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-md text-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-900 rounded-md text-xs flex items-center justify-between">
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-3">
        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 px-2 py-1 flex items-center justify-around text-[11px] font-medium text-slate-600">
        <a href="{{ route('resident.home') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 {{ request()->routeIs('resident.home') ? 'text-teal-800 font-bold' : 'hover:text-slate-900' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Home</span>
        </a>

        <a href="{{ route('resident.reports.create') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 {{ request()->routeIs('resident.reports.create') ? 'text-teal-800 font-bold' : 'hover:text-slate-900' }}">
            <div class="w-5 h-5 rounded bg-teal-800 text-white flex items-center justify-center font-bold text-xs">
                +
            </div>
            <span>Report</span>
        </a>

        <a href="{{ route('resident.reports.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 {{ request()->routeIs('resident.reports.*') && !request()->routeIs('resident.reports.create') ? 'text-teal-800 font-bold' : 'hover:text-slate-900' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>My Reports</span>
        </a>

        <a href="{{ route('resident.notifications') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 relative {{ request()->routeIs('resident.notifications') ? 'text-teal-800 font-bold' : 'hover:text-slate-900' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @php $unreadCount = auth()->check() ? auth()->user()->notifications()->where('is_read', false)->count() : 0; @endphp
            @if($unreadCount > 0)
                <span class="absolute top-1 right-3 w-1.5 h-1.5 bg-rose-600 rounded-full"></span>
            @endif
            <span>Alerts</span>
        </a>

        <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 {{ request()->routeIs('profile') ? 'text-teal-800 font-bold' : 'hover:text-slate-900' }}">
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
