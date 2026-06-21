<?php
$variant = $variant ?? 'default';
$size = $size ?? 'default';
$icon = $icon ?? null;
$href = $href ?? null;
$type = $href ? null : ($type ?? 'button');
$ariaLabel = $ariaLabel ?? null;

$baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = match($variant) {
    'primary' => 'bg-blue-900 text-white hover:bg-blue-800 focus:ring-blue-500',
    'secondary' => 'bg-white text-slate-700 border border-gray-200 hover:bg-gray-50 focus:ring-gray-500',
    'success' => 'bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-emerald-500',
    'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500',
    'danger' => 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500',
    'ghost' => 'text-slate-600 hover:bg-slate-100 focus:ring-slate-500',
    'navy' => 'text-blue-700 hover:bg-blue-50 focus:ring-blue-500',
    'amber' => 'text-amber-700 hover:bg-amber-50 focus:ring-amber-500',
    'slate' => 'text-slate-600 hover:bg-slate-50 focus:ring-slate-500',
    default => 'bg-white text-slate-700 border border-gray-200 hover:bg-gray-50 focus:ring-gray-500',
};

$sizeClasses = match($size) {
    'sm' => 'px-2.5 py-1.5 text-xs rounded-md gap-1',
    'lg' => 'px-5 py-2.5 text-base rounded-lg gap-2',
    default => 'px-3 py-1.5 text-sm rounded-lg gap-1.5',
};

$classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
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