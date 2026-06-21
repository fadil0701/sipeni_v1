<?php
$action = $action ?? '#';
$method = $method ?? 'GET';
$placeholder = $placeholder ?? 'Cari...';
$filters = $filters ?? [];
?>

<form action="{{ $action }}" method="{{ $method }}" class="flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="{{ $placeholder }}"
            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-white text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
        >
    </div>

    @if(count($filters) > 0)
        @foreach($filters as $filter)
            <select
                name="{{ $filter['name'] }}"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
                <option value="">{{ $filter['label'] }}</option>
                @foreach($filter['options'] as $option)
                    <option value="{{ $option['value'] }}" {{ request($filter['name']) == $option['value'] ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        @endforeach
    @endif

    <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800 transition-colors"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Cari
    </button>
</form>