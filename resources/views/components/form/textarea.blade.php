@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 3,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'error' => '',
    'help' => '',
    'col' => 'col-12',
])

<div class="{{ $col }}">
    @if($label)
    <label for="{{ $name }}" class="form-label fw-semibold mb-2">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    @endif
    <textarea 
        id="{{ $name }}" 
        name="{{ $name }}" 
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        @if($disabled) disabled @endif
        class="form-control @if($error) is-invalid @endif"
    >{{ old($name, $value) }}</textarea>
    @if($error)
    <div class="invalid-feedback">{{ $error }}</div>
    @endif
    @if($help)
    <small class="text-muted">{{ $help }}</small>
    @endif
</div>