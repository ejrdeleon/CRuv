@props([
    'image' => null,
    'src' => null,
    'thumb' => null,
    'alt' => 'Evidence photo',
    'caption' => null,
    'size' => 'detail',
    'lightbox' => true,
    'rounded' => 'rounded-md',
])

@php
    $fullUrl = $src ?? ($image?->url ?? '');
    $thumbUrl = $thumb ?? ($image?->thumbnail_url ?? $fullUrl);
    $captionText = $caption ?? ($image?->is_resolution_proof ? 'Resolution Proof' : ($image?->caption ?? ''));

    $sizeClasses = match($size) {
        'thumb' => 'w-10 h-10 shrink-0',
        'table' => 'w-12 h-12 shrink-0',
        'drawer' => 'w-full h-32',
        'preview' => 'w-full h-44',
        'detail' => 'w-full h-60 sm:h-72',
        default => 'w-full h-60',
    };
@endphp

<div
    class="relative {{ $sizeClasses }} {{ $rounded }} overflow-hidden bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 flex items-center justify-center group select-none {{ $lightbox ? 'cursor-zoom-in' : '' }}"
    @if($lightbox)
        @click.stop="$dispatch('open-lightbox', { url: @js($fullUrl), caption: @js($captionText), alt: @js($alt) })"
        title="Click to view full photo"
    @endif
>
    <img
        src="{{ $thumbUrl }}"
        alt="{{ $alt }}"
        class="w-full h-full object-contain transition-transform duration-150 group-hover:scale-[1.02]"
        loading="lazy"
        decoding="async"
    >

    @if($lightbox && $size !== 'thumb' && $size !== 'table')
        <div class="absolute top-2 right-2 p-1 rounded bg-slate-900/70 text-white opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
            </svg>
        </div>
    @endif

    @if(!empty($captionText) && ($size === 'detail' || $size === 'drawer'))
        <div class="absolute bottom-0 inset-x-0 p-1.5 bg-slate-950/85 text-[10px] text-slate-200 truncate font-medium">
            {{ $captionText }}
        </div>
    @endif
</div>
