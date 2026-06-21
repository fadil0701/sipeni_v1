<?php
$title = $title ?? '';
$description = $description ?? null;
$actions = $actions ?? null;
?>

<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
            @if($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>
        @if($actions)
            <div class="flex items-center gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>