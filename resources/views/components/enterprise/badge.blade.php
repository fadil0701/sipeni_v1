<?php
$variant = $variant ?? 'default';
$size = $size ?? 'default';
$dot = $dot ?? false;
$icon = $icon ?? null;
$status = $status ?? null;

$variantClasses = match ($variant) {
    'success' => \App\Support\UiColor::badge('success'),
    'warning' => \App\Support\UiColor::badge('warning'),
    'danger' => \App\Support\UiColor::badge('danger'),
    'info' => \App\Support\UiColor::badge('info'),
    'primary' => \App\Support\UiColor::badge('primary'),
    'neutral', 'default' => \App\Support\UiColor::badge('neutral'),
    // Role groups (kategori, bukan workflow status)
    'purple', 'struktural' => 'bg-violet-100 text-violet-900',
    'orange', 'operator' => 'bg-orange-100 text-orange-900',
    'system' => 'bg-blue-900 text-white',
    'manajerial' => \App\Support\UiColor::badge('success'),
    'unit' => \App\Support\UiColor::badge('neutral'),
    default => $status !== null
        ? \App\Support\UiColor::badgeForStatus($status)
        : \App\Support\UiColor::badge('neutral'),
};

$sizeClasses = match ($size) {
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
