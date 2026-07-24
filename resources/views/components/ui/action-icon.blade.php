@props([
    'action' => 'detail',
    'size' => 'md',
])

@php
    $key = strtolower(str_replace([' ', '-'], '_', trim((string) $action)));
    // Alias → ikon kanonik
    $key = match ($key) {
        'lihat', 'view', 'show' => 'detail',
        'process' => 'proses',
        'ketahui' => 'mengetahui',
        'approve', 'menyetujui' => 'setujui',
        'verify' => 'verifikasi',
        'submit' => 'ajukan',
        'barang_tersedia' => 'setujui',
        'lanjut_perbaikan', 'warning' => 'lanjut',
        'reject', 'kembalikan' => 'tolak',
        'delete', 'destroy' => 'hapus',
        'create' => 'tambah',
        'save' => 'simpan',
        'cancel' => 'batal',
        default => $key,
    };

    $iconClass = match ($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'icon' => 'w-5 h-5',
        default => 'w-4 h-4',
    };

    /**
     * Path SVG (Heroicons outline) — satu ikon per aksi.
     * @var array<string, list<string>>
     */
    $paths = match ($key) {
        'detail' => [
            'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        ],
        'proses' => [
            'M13 7l5 5m0 0l-5 5m5-5H6',
        ],
        'disposisi' => [
            'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
        ],
        'mengetahui' => [
            'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'setujui' => [
            'M5 13l4 4L19 7',
        ],
        'verifikasi' => [
            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        ],
        'ajukan' => [
            'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'lanjut' => [
            'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        ],
        'tolak' => [
            'M6 18L18 6M6 6l12 12',
        ],
        'hapus' => [
            'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        ],
        'tambah' => [
            'M12 4v16m8-8H4',
        ],
        'edit' => [
            'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        ],
        'simpan' => [
            'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4',
        ],
        'batal', 'kembali' => [
            'M10 19l-7-7m0 0l7-7m-7 7h18',
        ],
        default => [
            'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    };
@endphp

<svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24', 'aria-hidden' => 'true']) }}>
    @foreach($paths as $d)
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $d }}" />
    @endforeach
</svg>
