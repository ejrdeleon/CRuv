<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">

    <title>{{ $title ?? 'Admin Dashboard' }} - Barangay San Isidro</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex flex-col min-h-screen">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-64 bg-slate-900 text-slate-300 border-r border-slate-800 shrink-0">
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 gap-3 border-b border-slate-800 bg-slate-950/50">
                <div class="w-8 h-8 rounded-lg bg-teal-500 text-slate-950 flex items-center justify-center font-bold text-base shadow">
                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-tight">Barangay Admin</h1>
                    <p class="text-[11px] text-teal-400 font-medium">Monitoring & Operations</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-teal-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Dashboard Overview</span>
                </a>

                <a href="{{ route('admin.map') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.map') ? 'bg-teal-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span>Interactive Map</span>
                </a>

                <a href="{{ route('admin.reports') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.reports*') ? 'bg-teal-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span>Report Management</span>
                    </div>
                    @php $pendingCount = \App\Models\Report::whereIn('status', ['submitted', 'under_review'])->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-500 text-slate-950">{{ $pendingCount }}</span>
                    @endif
                </a>

                @if(auth()->user()->isAdmin())
                    <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        Administration
                    </div>

                    <a href="{{ route('admin.staff') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.staff') ? 'bg-teal-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Staff & Departments</span>
                    </a>

                    <a href="{{ route('admin.analytics') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.analytics') ? 'bg-teal-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Analytics & Exports</span>
                    </a>
                @endif

                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    Switch Views
                </div>

                <a href="{{ route('resident.home') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Resident Mobile View</span>
                </a>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-800/60">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-teal-500/20 text-teal-300 font-bold flex items-center justify-center text-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold {{ auth()->user()->isAdmin() ? 'bg-purple-900/60 text-purple-300' : 'bg-blue-900/60 text-blue-300' }}">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Log Out" class="p-1.5 text-slate-400 hover:text-rose-400 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
            <!-- Top Bar for Tablet/Desktop -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-slate-800">{{ $headerTitle ?? 'Community Operations Dashboard' }}</span>
                    <span class="hidden sm:inline-block px-2 py-0.5 rounded text-xs bg-teal-50 text-teal-700 font-medium border border-teal-200/60">
                        Barangay San Isidro, Bay, Laguna
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Quick links on mobile -->
                    <div class="flex lg:hidden items-center gap-1">
                        <a href="{{ route('admin.dashboard') }}" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </a>
                    </div>

                    <a href="{{ route('resident.reports.create') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>New Report</span>
                    </a>

                    <a href="{{ route('profile') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 px-2 py-1">
                        Profile
                    </a>
                </div>
            </header>

            <!-- Alerts Banner -->
            <div class="px-4 sm:px-6 pt-3">
                @if (session('success'))
                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm flex items-center justify-between">
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
