@props(['cancelUrl', 'cancelLabel' => 'Batal', 'submitLabel' => 'Simpan'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap justify-end gap-2 border-t border-slate-200 pt-4']) }}>
    <a href="{{ $cancelUrl }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ $cancelLabel }}</a>
    {{ $slot }}
    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ $submitLabel }}</button>
</div>
