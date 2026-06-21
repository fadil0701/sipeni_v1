@props([
    'label' => '',
    'name' => '',
    'options' => [],
    'value' => '',
    'placeholder' => 'Pilih...',
    'required' => false,
    'disabled' => false,
    'error' => '',
    'help' => '',
    'col' => 'col-12',
    'optionValue' => 'id',
    'optionLabel' => 'nama',
])

<div class="{{ $col }}">
    @if($label)
    <label for="{{ $name }}" class="form-label fw-semibold mb-2">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    @endif
    <select 
        id="{{ $name }}" 
        name="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        class="form-select @if($error) is-invalid @endif"
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $option)
            <option value="{{ $option->$optionValue }}" {{ old($name, $value) == $option->$optionValue ? 'selected' : '' }}>
                {{ $option->$optionLabel }}
            </option>
        @endforeach
    </select>
    @if($error)
    <div class="invalid-feedback">{{ $error }}</div>
    @endif
    @if($help)
    <small class="text-muted">{{ $help }}</small>
    @endif
</div>