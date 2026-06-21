<?php
$size = $size ?? 'default';
$icon = $icon ?? null;

$sizeClasses = match($size) {
    'sm' => 'text-base',
    'lg' => 'text-xl',
    'xl' => 'text-2xl',
    default => 'text-lg',
};
?>

<div class="flex items-center gap-2 mb-4">
    @if($icon)
        <div class="text-slate-500">
            {!! $icon !!}
        </div>
    @endif
    <h2 class="{{ $sizeClasses }} font-semibold text-slate-800">{{ $slot }}</h2>
</div>