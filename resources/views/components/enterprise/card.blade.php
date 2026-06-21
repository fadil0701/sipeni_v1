<?php
$bordered = $bordered ?? true;
$padding = $padding ?? true;
$hover = $hover ?? false;
?>

@if($bordered)
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm {{ $hover ? 'transition-all duration-200 hover:shadow-md hover:-translate-y-0.5' : '' }}">
        @if(isset($header))
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ $header }}</h2>
            </div>
        @endif
        <div class="{{ $padding ? 'p-6' : '' }}">
            {{ $slot }}
        </div>
    </div>
@else
    <div class="bg-white {{ $hover ? 'transition-all duration-200 hover:shadow-md hover:-translate-y-0.5' : '' }}">
        @if(isset($header))
            <div class="px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ $header }}</h2>
            </div>
        @endif
        <div class="{{ $padding ? 'p-6' : '' }}">
            {{ $slot }}
        </div>
    </div>
@endif