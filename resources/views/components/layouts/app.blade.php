<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d9488">
    <meta name="description" content="Community Damage Reporting and Monitoring System">

    <title>{{ $title ?? config('app.name', 'Barangay Community Reporting') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col min-h-screen">

    {{ $slot }}

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
