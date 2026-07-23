{{-- Shared sparepart rows for create/edit; expect $sparepartRows (collection|array) --}}
@php
    $sparepartRows = $sparepartRows ?? collect();
    if ($sparepartRows instanceof \Illuminate\Support\Collection && $sparepartRows->isEmpty() && old('spareparts')) {
        $sparepartRows = collect(old('spareparts'));
    } elseif (! old('spareparts') && $sparepartRows instanceof \Illuminate\Support\Collection && $sparepartRows->isEmpty()) {
        $sparepartRows = collect([['nama_sparepart' => '', 'merk' => '', 'nomor_seri' => '']]);
    }
    if (is_array($sparepartRows)) {
        $sparepartRows = collect($sparepartRows);
    }
@endphp
<div id="sparepart-section" class="hidden space-y-3 rounded-md border border-gray-200 bg-white p-4">
    <div class="flex items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Daftar Spare Part (pengajuan pembelian)</h3>
            <p class="text-xs text-gray-500">Wajib diisi jika rekomendasi Pending. Foto tampil sebagai thumbnail kecil — klik untuk memperbesar.</p>
        </div>
        <button type="button" id="btn-add-sparepart" class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
            + Tambah
        </button>
    </div>
    @error('spareparts')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <div class="overflow-x-auto rounded-md border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Spare Part</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Seri</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                    <th class="px-3 py-2 w-12"></th>
                </tr>
            </thead>
            <tbody id="sparepart-tbody" class="bg-white divide-y divide-gray-200">
                @foreach($sparepartRows as $idx => $row)
                    @php
                        $rowId = is_object($row) ? ($row->id_service_report_sparepart ?? null) : ($row['id'] ?? null);
                        $nama = is_object($row) ? ($row->nama_sparepart ?? '') : ($row['nama_sparepart'] ?? '');
                        $merk = is_object($row) ? ($row->merk ?? '') : ($row['merk'] ?? '');
                        $nomorSeri = is_object($row) ? ($row->nomor_seri ?? '') : ($row['nomor_seri'] ?? '');
                        $fotoPath = is_object($row) ? ($row->foto_path ?? null) : null;
                        $fotoInputId = 'sparepart_foto_'.$idx.'_'.uniqid();
                        $fotoUrl = $fotoPath ? route('media.show', ['path' => $fotoPath]) : null;
                    @endphp
                    <tr class="sparepart-row">
                        <td class="px-3 py-2 align-top">
                            @if($rowId)
                                <input type="hidden" name="spareparts[{{ $idx }}][id]" value="{{ $rowId }}">
                            @endif
                            <input type="text" name="spareparts[{{ $idx }}][nama_sparepart]" value="{{ $nama }}" class="block w-full border border-gray-300 rounded-md px-2 py-1.5" placeholder="Nama spare part">
                        </td>
                        <td class="px-3 py-2 align-top">
                            <input type="text" name="spareparts[{{ $idx }}][merk]" value="{{ $merk }}" class="block w-full border border-gray-300 rounded-md px-2 py-1.5" placeholder="Merk">
                        </td>
                        <td class="px-3 py-2 align-top">
                            <input type="text" name="spareparts[{{ $idx }}][nomor_seri]" value="{{ $nomorSeri }}" class="block w-full border border-gray-300 rounded-md px-2 py-1.5" placeholder="No. seri">
                        </td>
                        <td class="px-3 py-2 align-top">
                            <div class="flex flex-col items-start gap-2">
                                <input type="file" id="{{ $fotoInputId }}" name="spareparts[{{ $idx }}][foto]" accept="image/*" class="sr-only sparepart-foto-input">
                                <label for="{{ $fotoInputId }}" class="inline-flex items-center px-3 py-1.5 border border-blue-200 bg-blue-50 text-blue-700 text-xs font-medium rounded-md cursor-pointer hover:bg-blue-100">
                                    Pilih Foto
                                </label>
                                <button
                                    type="button"
                                    class="sparepart-foto-thumb {{ $fotoUrl ? '' : 'hidden' }} block w-14 h-14 rounded border border-gray-200 overflow-hidden bg-gray-50 p-0 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    title="Klik untuk memperbesar"
                                    data-full-src="{{ $fotoUrl }}"
                                >
                                    <img
                                        src="{{ $fotoUrl }}"
                                        alt="Thumbnail spare part"
                                        class="sparepart-foto-thumb-img w-full h-full object-cover pointer-events-none"
                                    >
                                </button>
                                <p class="sparepart-foto-name text-xs text-gray-500 {{ $fotoUrl ? '' : '' }}">{{ $fotoUrl ? 'Klik foto untuk memperbesar' : 'Belum ada foto' }}</p>
                            </div>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <button type="button" class="btn-remove-sparepart inline-flex items-center justify-center w-8 h-8 rounded-full text-red-600 hover:bg-red-50" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Lightbox: thumbnail klik → perbesar --}}
<div id="sparepart-foto-lightbox" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-label="Preview foto spare part">
    <button type="button" id="sparepart-foto-lightbox-close" class="absolute top-4 right-4 rounded-md bg-white/90 px-3 py-1.5 text-sm font-medium text-gray-800 hover:bg-white">Tutup</button>
    <img id="sparepart-foto-lightbox-img" src="" alt="Preview besar" class="max-h-[90vh] max-w-[95vw] rounded-lg shadow-xl object-contain bg-white">
</div>

@once
@push('scripts')
<script>
window.SipeniSparepartFoto = window.SipeniSparepartFoto || (function () {
    function openLightbox(src) {
        const lb = document.getElementById('sparepart-foto-lightbox');
        const img = document.getElementById('sparepart-foto-lightbox-img');
        if (!lb || !img || !src) return;
        img.src = src;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
    }

    function closeLightbox() {
        const lb = document.getElementById('sparepart-foto-lightbox');
        const img = document.getElementById('sparepart-foto-lightbox-img');
        if (!lb) return;
        lb.classList.add('hidden');
        lb.classList.remove('flex');
        if (img) img.removeAttribute('src');
    }

    function isImageFile(file) {
        if (!file) return false;
        if (file.type && file.type.startsWith('image/')) return true;
        return /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(file.name || '');
    }

    function bindOnce() {
        if (window.__sipeniSparepartFotoBound) return;
        window.__sipeniSparepartFotoBound = true;

        document.getElementById('sparepart-foto-lightbox-close')?.addEventListener('click', closeLightbox);
        document.getElementById('sparepart-foto-lightbox')?.addEventListener('click', function (e) {
            if (e.target === this) closeLightbox();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
        });

        document.addEventListener('click', function (e) {
            const thumb = e.target.closest('.sparepart-foto-thumb');
            if (!thumb || thumb.disabled) return;
            const src = thumb.getAttribute('data-full-src') || thumb.querySelector('img')?.getAttribute('src');
            if (src) openLightbox(src);
        });
    }

    document.addEventListener('DOMContentLoaded', bindOnce);
    if (document.readyState !== 'loading') bindOnce();

    return { openLightbox, closeLightbox, isImageFile };
})();
</script>
@endpush
@endonce
