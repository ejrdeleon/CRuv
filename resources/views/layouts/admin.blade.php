<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">

    <title>{{ $title ?? 'Admin Operations' }} - {{ config('community.municipality', 'Santa Cruz') }}, {{ config('community.province', 'Laguna') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    <!-- Dark Mode Default + Persistent localStorage Theme Initializer -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('cruv_admin_theme') || 'dark';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body
    class="h-full font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-100 dark:bg-slate-950 flex flex-col min-h-screen"
    x-data="{
        mobileMenuOpen: false,
        sidebarCollapsed: localStorage.getItem('cruv_sidebar_collapsed') === 'true',
        darkMode: (localStorage.getItem('cruv_admin_theme') || 'dark') === 'dark',
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('cruv_admin_theme', this.darkMode ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.darkMode);
            setTimeout(() => window.CivicSpatialMap?.invalidateAll(), 60);
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('cruv_sidebar_collapsed', this.sidebarCollapsed ? 'true' : 'false');
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
                window.CivicSpatialMap?.invalidateAll();
            }, 180);
        }
    }"
>

    <div class="flex h-screen overflow-hidden">
        <!-- Collapsible Left Sidebar (Visible on Tablet 768px+ and Desktop 1440px+) -->
        <aside
            :class="sidebarCollapsed ? 'md:w-16' : 'md:w-56 lg:w-60'"
            class="hidden md:flex md:flex-col bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800 shrink-0 transition-all duration-150"
        >
            <!-- Brand Header & Collapse Button -->
            <div class="h-14 flex items-center px-3 border-b border-slate-200 dark:border-slate-800" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div class="flex items-center gap-2.5 min-w-0" :class="sidebarCollapsed ? 'cursor-pointer' : ''" @click="sidebarCollapsed ? toggleSidebar() : null">
                    <div class="w-7 h-7 rounded-md bg-teal-700 text-white flex items-center justify-center font-bold text-xs shrink-0" :title="sidebarCollapsed ? 'Click to expand' : ''">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" class="min-w-0">
                        <h1 class="text-xs font-bold text-slate-900 dark:text-white tracking-tight truncate">{{ config('community.municipality', 'Santa Cruz') }}, {{ config('community.province', 'Laguna') }}</h1>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium truncate">Civic Incident Portal</p>
                    </div>
                </div>
                <button
                    type="button"
                    x-show="!sidebarCollapsed"
                    @click="toggleSidebar()"
                    title="Collapse Sidebar"
                    class="p-1 rounded text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Flat Icon Navigation Links (Overview, Issue Queue, Map View, Analytics, Settings) -->
            <nav class="flex-1 px-2 py-3 space-y-1 overflow-y-auto text-xs">
                <a href="{{ route('admin.dashboard') }}" title="Overview"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2.5'"
                    class="flex items-center gap-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Overview</span>
                </a>

                <a href="{{ route('admin.reports') }}" title="Issue Queue"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2.5'"
                    class="flex items-center justify-between py-2 rounded-md font-medium transition relative {{ request()->routeIs('admin.reports*') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Issue Queue</span>
                    </div>
                    @php $pendingCount = \App\Models\Report::whereIn('status', ['submitted', 'under_review'])->count(); @endphp
                    @if($pendingCount > 0)
                        <span x-show="!sidebarCollapsed" class="px-1.5 py-0.2 text-[10px] font-bold rounded bg-amber-500 text-slate-950 tabular-nums-metric">{{ $pendingCount }}</span>
                        <span x-show="sidebarCollapsed" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white dark:ring-slate-900"></span>
                    @endif
                </a>

                <a href="{{ route('admin.map') }}" title="Map View"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2.5'"
                    class="flex items-center gap-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.map') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Map View</span>
                </a>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.analytics') }}" title="Analytics"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2.5'"
                        class="flex items-center gap-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.analytics') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Analytics</span>
                    </a>

                    <a href="{{ route('admin.staff') }}" title="Settings (Departments & Categories)"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2.5'"
                        class="flex items-center gap-2.5 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.staff') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Settings</span>
                    </a>
                @endif
            </nav>

            <!-- User Info & Logout -->
            <div class="p-2 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center p-2 rounded-md bg-slate-100 dark:bg-slate-800/70" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded bg-teal-700 text-white font-bold flex items-center justify-center text-[11px] shrink-0" :title="sidebarCollapsed ? '{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})' : ''">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div x-show="!sidebarCollapsed" class="min-w-0">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-block text-[10px] font-medium text-slate-500 dark:text-slate-400">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" x-show="!sidebarCollapsed">
                        @csrf
                        <button type="submit" title="Sign Out" class="p-1.5 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Mobile Slide-Over Navigation (<768px) -->
        <div x-show="mobileMenuOpen" style="display: none;" class="fixed inset-0 z-50 md:hidden flex">
            <div class="fixed inset-0 bg-slate-950/70" @click="mobileMenuOpen = false"></div>
            <div class="relative w-64 max-w-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 flex flex-col h-full z-10 border-r border-slate-200 dark:border-slate-800">
                <div class="h-14 flex items-center justify-between px-4 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ config('community.full_location', 'Santa Cruz, Laguna, Philippines') }} Admin</span>
                    <button type="button" @click="mobileMenuOpen = false" class="p-1 text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
                </div>
                <nav class="flex-1 px-3 py-3 space-y-1 text-xs overflow-y-auto">
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">Overview</a>
                    <a href="{{ route('admin.reports') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.reports*') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">Issue Queue</a>
                    <a href="{{ route('admin.map') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.map') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">Map View</a>
                    <a href="{{ route('admin.analytics') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.analytics') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">Analytics</a>
                    <a href="{{ route('admin.staff') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.staff') ? 'bg-teal-700 text-white font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50 dark:bg-slate-950">
            <!-- Top Bar -->
            <header class="h-14 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button" @click="mobileMenuOpen = true" class="md:hidden p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md" aria-label="Open menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">{{ $headerTitle ?? 'Operations Workspace' }}</span>
                    <!-- Keyboard Shortcut Hints -->
                    <div class="hidden xl:flex items-center gap-2 ml-3 text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[10px]">J/K</span>
                        <span>Navigate Rows</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[10px]">Enter</span>
                        <span>Inspect Drawer</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[10px]">S</span>
                        <span>Status</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[10px]">A</span>
                        <span>Assign</span>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <!-- Persistent Light / Dark Theme Toggle -->
                    <button
                        type="button"
                        @click="toggleTheme()"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition"
                        title="Toggle Light / Dark Theme"
                    >
                        <svg x-show="darkMode" class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!darkMode" style="display:none;" class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                    </button>

                    <a href="{{ route('profile') }}" class="text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white px-2 py-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">
                        Profile
                    </a>
                </div>
            </header>

            <!-- Alerts Banner -->
            <div class="px-4 sm:px-6 pt-3 empty:hidden">
                @if (session('success'))
                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 rounded-md text-xs flex items-center justify-between">
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 rounded-md text-xs flex items-center justify-between">
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-5">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Global Full-Resolution Photo Lightbox Modal -->
    <div
        x-data="{
            isOpen: false,
            url: '',
            caption: '',
            alt: '',
            isZoomed: false,
            open(detail) {
                this.url = detail.url || '';
                this.caption = detail.caption || '';
                this.alt = detail.alt || 'Evidence photo';
                this.isZoomed = false;
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            close() {
                this.isOpen = false;
                this.isZoomed = false;
                this.url = '';
                document.body.classList.remove('overflow-hidden');
            },
            toggleZoom() {
                this.isZoomed = !this.isZoomed;
            }
        }"
        @open-lightbox.window="open($event.detail)"
        @keydown.escape.window="if (isOpen) close()"
        x-show="isOpen"
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 select-none"
        role="dialog"
        aria-modal="true"
        aria-label="Photo evidence lightbox"
    >
        <!-- Backdrop click target -->
        <div class="absolute inset-0" @click="close()"></div>

        <!-- Top Control Bar -->
        <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
            <button
                type="button"
                @click="toggleZoom()"
                class="px-2.5 py-1.5 rounded-md bg-slate-800/90 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-medium border border-slate-700 shadow-sm transition inline-flex items-center gap-1.5"
                :title="isZoomed ? 'Reset zoom' : 'Zoom 2x'"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                </svg>
                <span x-text="isZoomed ? '1x View' : '2x Zoom'"></span>
            </button>

            <a
                :href="url"
                target="_blank"
                class="px-2.5 py-1.5 rounded-md bg-slate-800/90 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-medium border border-slate-700 shadow-sm transition inline-flex items-center gap-1.5"
                title="Open original file in new tab"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Original</span>
            </a>

            <button
                type="button"
                @click="close()"
                class="p-1.5 rounded-md bg-slate-800/90 hover:bg-rose-900/90 text-slate-200 hover:text-white border border-slate-700 shadow-sm transition"
                title="Close lightbox (Esc)"
                aria-label="Close"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Lightbox Content Container -->
        <div
            class="relative z-1 max-w-full max-h-full flex flex-col items-center justify-center p-2"
            :class="isZoomed ? 'overflow-auto max-h-[92vh] max-w-[94vw]' : ''"
            @click.stop
        >
            <img
                :src="url"
                :alt="alt"
                class="object-contain transition-all duration-200 rounded select-none cursor-pointer"
                :class="isZoomed ? 'max-w-none max-h-none w-[180%] h-auto' : 'max-h-[82vh] max-w-[88vw] w-auto h-auto'"
                @click="toggleZoom()"
            >

            <div x-show="caption" class="mt-2.5 px-3 py-1.5 rounded bg-slate-900/80 border border-slate-800 text-xs text-slate-300 text-center max-w-xl">
                <p x-text="caption"></p>
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
