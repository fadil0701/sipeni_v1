<?php
$variant = $variant ?? 'default';
$icon = $icon ?? null;
$trend = $trend ?? null;
$trendUp = $trendUp ?? true;
?>

<div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:shadow-md">
    <div class="flex items-center gap-3">
        @if($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg
                {{ $variant === 'success' ? 'bg-emerald-50 text-emerald-600' : '' }}
                {{ $variant === 'warning' ? 'bg-amber-50 text-amber-600' : '' }}
                {{ $variant === 'danger' ? 'bg-red-50 text-red-600' : '' }}
                {{ $variant === 'info' ? 'bg-blue-50 text-blue-600' : '' }}
                {{ $variant === 'default' ? 'bg-slate-50 text-slate-600' : '' }}
            ">
                {{ $icon }}
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="text-sm text-slate-500 truncate">{{ $label }}</p>
            <p class="text-xl font-semibold text-slate-900">{{ $value }}</p>
            @if($trend)
                <p class="text-xs mt-0.5 {{ $trendUp ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $trend }}
                </p>
            @endif
        </div>
    </div>
</div>