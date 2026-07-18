@props([
    'tone' => 'primary',
    'soft' => false,
    'href' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'px-2.5 py-1.5 text-xs rounded-md gap-1',
        'lg' => 'px-5 py-2.5 text-base rounded-lg gap-2',
        'icon' => 'p-2 rounded-md',
        default => 'px-3 py-1.5 text-sm rounded-lg gap-1.5',
    };

    $toneClasses = $soft
        ? \App\Support\UiColor::softButton($tone)
        : \App\Support\UiColor::button($tone);

    $base = 'inline-flex items-center justify-center font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed '.$sizeClasses.' '.$toneClasses;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base]) }}>
        {{ $slot }}
    </button>
@endif
