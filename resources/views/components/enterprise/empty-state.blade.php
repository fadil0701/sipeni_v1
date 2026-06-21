<?php
$icon = $icon ?? null;
$title = $title ?? 'Tidak ada data';
$description = $description ?? null;
$action = $action ?? null;
?>

<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    @if($icon)
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400 mb-4">
            {!! $icon !!}
        </div>
    @endif

    <h3 class="text-sm font-semibold text-slate-900 mb-1">{{ $title }}</h3>

    @if($description)
        <p class="text-sm text-slate-500 mb-4 max-w-sm">{{ $description }}</p>
    @endif

    @if($action)
        <div class="mt-2">
            {{ $action }}
        </div>
    @endif
</div>