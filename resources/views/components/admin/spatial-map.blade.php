@props([
    'mapId' => 'shared-civic-map',
    'markers' => [],
    'height' => '460px',
    'title' => 'Live Spatial Map',
    'subtitle' => 'Click a row to pan & open drawer, or click a pin to highlight its row.',
    'showExpandLink' => true,
])

<div
    class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 p-3.5 space-y-2.5"
    x-data="{
        mapId: @js($mapId),
        bootMap() {
            const rawEl = this.$refs.markersPayload;
            const markers = rawEl ? JSON.parse(rawEl.dataset.markers || '[]') : [];
            if (window.CivicSpatialMap) {
                window.CivicSpatialMap.mount(this.mapId, markers);
            }
        }
    }"
    x-init="
        $nextTick(() => bootMap());
        window.addEventListener('civic-map-ready', () => bootMap());
    "
>
    <!-- Reactive markers payload bridge (outside wire:ignore so filter updates sync to the map) -->
    <div
        x-ref="markersPayload"
        data-markers="{{ json_encode($markers) }}"
        class="hidden"
        x-effect="
            const parsed = JSON.parse($el.dataset.markers || '[]');
            if (window.CivicSpatialMap) {
                window.CivicSpatialMap.mount(mapId, parsed);
            }
        "
    ></div>

    <div class="flex items-center justify-between gap-2">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ $title }}</h2>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800 tabular-nums-metric">
                    {{ count($markers) }} {{ \Illuminate\Support\Str::plural('pin', count($markers)) }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        </div>
        @if($showExpandLink)
            <a href="{{ route('admin.map') }}" class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline shrink-0">
                Full Map →
            </a>
        @endif
    </div>

    <div class="rounded-md border border-slate-200 dark:border-slate-800 overflow-hidden relative" wire:ignore>
        <div id="{{ $mapId }}" style="height: {{ $height }}; min-height: 360px; width: 100%;"></div>
    </div>

    <!-- Shared Flat Status Tokens Legend (Solid Dot + Text Label) -->
    <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400 pt-1 flex-wrap gap-2">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-hazard-dot)]"></span> Critical Hazard
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-progress-dot)]"></span> In Progress
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-low-dot)]"></span> Low Priority
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[var(--color-status-resolved-dot)]"></span> Resolved
            </span>
        </div>
        <span class="text-[10px] text-slate-400 font-mono">Santa Cruz, Laguna (14.2816, 121.4146)</span>
    </div>
</div>
