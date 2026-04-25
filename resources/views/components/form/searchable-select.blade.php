@props([
    'id',
    'name',
    'label' => null,
    'required' => false,
    'placeholder' => 'Pilih opsi',
    'help' => null,
    'error' => null,
    'searchable' => true,
])

@php
    $fieldId = $id ?? $name;
    $isRequired = filter_var($required, FILTER_VALIDATE_BOOL);
    $errorKey = $error ?? $name;
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $fieldId }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }} @if($isRequired)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @if($isRequired) required @endif
        @if($searchable) data-searchable="true" @endif
        {{ $attributes->except(['class']) }}
        class="select-searchable {{ $attributes->get('class') }} block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error($errorKey) border-red-500 @enderror"
    >
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>

    @error($errorKey)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    @if($help)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif
</div>
