@props([
    'tone' => null,
    'action' => null,
    'soft' => false,
    'href' => null,
    'type' => 'button',
    'size' => 'md',
    'icon' => null,
    'iconOnly' => null,
])

@php
    /**
     * Tombol aksi seragam (UiColor + ikon kanonik).
     *
     * - action: detail|proses|disposisi|mengetahui|setujui|verifikasi|tolak|…
     * - size sm|icon → default icon-only (tanpa teks); md|lg → ikon + label
     * - iconOnly: true|false|null — override perilaku di atas
     * - soft=true: gaya baris tabel
     */
    if ($tone === null) {
        $tone = $action
            ? \App\Support\UiColor::toneForAction($action)
            : 'primary';
    }

    $showIcon = $icon === null ? ($action !== null && $action !== '') : (bool) $icon;

    // Baris tabel (sm): aksi dengan ikon = hanya ikon
    $isIconOnly = $iconOnly === null
        ? ($showIcon && in_array($size, ['sm', 'icon'], true))
        : (bool) $iconOnly;

    // Aksi baris tabel default soft
    if ($soft === false && in_array($size, ['sm', 'icon'], true) && $action !== null) {
        $soft = true;
    }

    $sizeClasses = match ($size) {
        'sm' => $isIconOnly ? 'p-2 text-xs rounded-md' : 'px-2.5 py-1.5 text-xs rounded-md gap-1',
        'lg' => 'px-5 py-2.5 text-base rounded-lg gap-2',
        'icon' => 'p-2 rounded-md',
        default => 'px-3 py-1.5 text-sm rounded-md gap-1.5',
    };

    $toneClasses = $soft
        ? \App\Support\UiColor::softButton($tone)
        : \App\Support\UiColor::button($tone);

    $base = 'inline-flex items-center justify-center font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed '.$sizeClasses.' '.$toneClasses;

    $actionLabel = $action ? \App\Support\UiColor::labelForAction($action) : null;
    $title = $attributes->get('title') ?: $actionLabel;
    $ariaLabel = $attributes->get('aria-label') ?: $actionLabel;

    if ($title) {
        $attributes = $attributes->merge(['title' => $title]);
    }
    if ($ariaLabel) {
        $attributes = $attributes->merge(['aria-label' => $ariaLabel]);
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
        @if($showIcon && $action)
            <x-ui.action-icon :action="$action" :size="$size === 'icon' ? 'icon' : ($isIconOnly ? 'sm' : $size)" />
        @endif
        @unless($isIconOnly)
            {{ $slot }}
        @endunless
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base]) }}>
        @if($showIcon && $action)
            <x-ui.action-icon :action="$action" :size="$size === 'icon' ? 'icon' : ($isIconOnly ? 'sm' : $size)" />
        @endif
        @unless($isIconOnly)
            {{ $slot }}
        @endunless
    </button>
@endif
