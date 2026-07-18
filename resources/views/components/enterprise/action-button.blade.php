<?php
$variant = $variant ?? 'default';
$size = $size ?? 'default';
$icon = $icon ?? null;
$href = $href ?? null;
$type = $href ? null : ($type ?? 'button');
$ariaLabel = $ariaLabel ?? null;

$baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = match ($variant) {
    'primary' => \App\Support\UiColor::button('primary'),
    'secondary' => \App\Support\UiColor::button('secondary'),
    'success' => \App\Support\UiColor::button('success'),
    'warning' => \App\Support\UiColor::button('warning'),
    'danger' => \App\Support\UiColor::button('danger'),
    'info' => \App\Support\UiColor::button('info'),
    'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-400',
    'navy' => \App\Support\UiColor::softButton('primary'),
    'amber' => \App\Support\UiColor::softButton('warning'),
    'slate' => \App\Support\UiColor::softButton('neutral'),
    default => \App\Support\UiColor::button('secondary'),
};

$sizeClasses = match ($size) {
    'sm' => 'px-2.5 py-1.5 text-xs rounded-md gap-1',
    'lg' => 'px-5 py-2.5 text-base rounded-lg gap-2',
    default => 'px-3 py-1.5 text-sm rounded-lg gap-1.5',
};

$classes = $baseClasses.' '.$variantClasses.' '.$sizeClasses;
?>

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" {{ $ariaLabel ? 'aria-label=' . $ariaLabel : '' }}>
        @if($icon)
            {!! $icon !!}
        @endif
        @if(isset($slot) && $slot->isNotEmpty())
            {{ $slot }}
        @endif
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" {{ $ariaLabel ? 'aria-label=' . $ariaLabel : '' }}>
        @if($icon)
            {!! $icon !!}
        @endif
        @if(isset($slot) && $slot->isNotEmpty())
            {{ $slot }}
        @endif
    </button>
@endif
