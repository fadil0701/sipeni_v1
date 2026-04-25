@props([
    'action',
    'method' => 'GET',
    'formId' => 'indexFilterForm',
    'buttonText' => 'Filter',
    'searchLabel' => 'Cari',
    'searchName' => 'search',
    'searchPlaceholder' => '',
    'searchValue' => null,
    'showSearch' => true,
    'stacked' => false,
])

@php
    $searchVal = $searchValue !== null ? $searchValue : request($searchName);
@endphp

<div data-index-filter-toolbar="1" {{ $attributes->class(['bg-white shadow-sm rounded-lg border border-gray-200 p-4 sm:p-5 mb-6']) }}>
    <form method="{{ $method }}" action="{{ $action }}" id="{{ $formId }}"
        @if($stacked)
            class="flex flex-col gap-4"
        @else
            class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:gap-x-5 lg:gap-y-3"
        @endif
    >
        {{ $before ?? '' }}

        @isset($filters)
            @if($stacked)
                <div class="w-full min-w-0">
                    {{ $filters }}
                </div>
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:gap-4">
            @else
                <div class="contents">
                    {{ $filters }}
                </div>
            @endif
        @endisset

        @if($showSearch)
            <div class="w-full min-w-0 @if($stacked) sm:flex-1 sm:min-w-[14rem] @else lg:flex-1 lg:min-w-[14rem] @endif">
                <label for="{{ $formId }}_{{ $searchName }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $searchLabel }}</label>
                <div class="relative">
                    <div class="absolute inset-y-1 left-1 flex items-center pl-3 pointer-events-none" aria-hidden="true">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="{{ $formId }}_{{ $searchName }}"
                        name="{{ $searchName }}"
                        value="{{ old($searchName, $searchVal) }}"
                        placeholder="{{ $searchPlaceholder }}"
                        class="block w-full rounded-md border border-gray-300 py-2 pl-10 pr-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>
        @endif

        <div class="w-full shrink-0 sm:w-auto sm:min-w-[7.5rem]">
            @if($showSearch || $stacked)
                <span class="mb-1 hidden text-sm font-medium text-gray-700 sm:block sm:invisible sm:select-none" aria-hidden="true">&nbsp;</span>
            @endif
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:min-w-[7.5rem]"
            >
                {{ $buttonText }}
            </button>
        </div>

        @if($stacked)
            @isset($filters)
                </div>
            @endisset
        @endif
    </form>
</div>
