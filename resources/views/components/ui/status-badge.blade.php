@props([
    'status' => null,
    'tone' => null,
    'label' => null,
])

@php
    $resolvedTone = $tone ?? \App\Support\UiColor::toneForStatus($status);
    $classes = \App\Support\UiColor::badge($resolvedTone);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '.$classes]) }}>
    {{ $label ?? $status ?? $slot }}
</span>
