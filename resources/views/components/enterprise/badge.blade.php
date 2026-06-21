<?php
$variant = $variant ?? 'default';
$size = $size ?? 'default';
$dot = $dot ?? false;
$icon = $icon ?? null;

$variantClasses = match($variant) {
    'success' => 'bg-emerald-50 text-emerald-700',
    'warning' => 'bg-amber-50 text-amber-700',
    'danger' => 'bg-red-50 text-red-700',
    'info' => 'bg-blue-50 text-blue-700',
    'primary' => 'bg-blue-900 text-white',
    'purple' => 'bg-violet-100 text-violet-800',
    'orange' => 'bg-orange-100 text-orange-800',
    'system' => 'bg-[#1E3A8A] text-white',
    'struktural' => 'bg-violet-100 text-violet-800',
    'manajerial' => 'bg-emerald-100 text-emerald-800',
    'operator' => 'bg-orange-100 text-orange-800',
    'unit' => 'bg-slate-200 text-slate-700',
    default => 'bg-slate-100 text-slate-600',
};

$sizeClasses = match($size) {
    'sm' => 'px-2 py-0.5 text-[10px]',
    'lg' => 'px-3 py-1 text-sm',
    default => 'px-2.5 py-0.5 text-xs',
};
?>

<span class="inline-flex items-center rounded-full {{ $variantClasses }} {{ $sizeClasses }} font-medium">
    @if($dot)
        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
    @endif
    @if($icon)
        <span class="mr-1">{!! $icon !!}</span>
    @endif
    {{ $slot }}
</span>