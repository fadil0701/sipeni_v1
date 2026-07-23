@extends('layouts.app')

@section('content')
<div class="mb-4"><a href="{{ route('maintenance.service-report.index') }}" class="text-blue-600 hover:text-blue-900">Kembali ke daftar</a></div>
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-xl font-semibold">Detail Laporan Servis</h2>
            <p class="text-sm text-gray-600">{{ $serviceReport->no_service_report }}</p>
        </div>
        <a href="{{ route('maintenance.service-report.edit', $serviceReport->id_service_report) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md">Edit</a>
    </div>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 text-sm">
        <div><dt class="text-gray-500">Nomor Register</dt><dd class="font-medium">{{ $serviceReport->registerAset->nomor_register ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Nama Barang</dt><dd class="font-medium">{{ $serviceReport->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Merk</dt><dd class="font-medium">{{ trim((string) ($serviceReport->registerAset->inventory->merk ?? '')) !== '' ? $serviceReport->registerAset->inventory->merk : '-' }}</dd></div>
        <div><dt class="text-gray-500">Tipe</dt><dd class="font-medium">{{ trim((string) ($serviceReport->registerAset->inventory->tipe ?? '')) !== '' ? $serviceReport->registerAset->inventory->tipe : '-' }}</dd></div>
        @php
            $noSeriShow = trim((string) (
                $serviceReport->registerAset->inventoryItem->no_seri
                ?? ($serviceReport->registerAset->inventory->no_seri ?? '')
            ));
        @endphp
        <div><dt class="text-gray-500">No. Seri</dt><dd class="font-medium">{{ $noSeriShow !== '' ? $noSeriShow : '-' }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Servis</dt><dd class="font-medium">{{ optional($serviceReport->tanggal_service)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Selesai</dt><dd class="font-medium">{{ optional($serviceReport->tanggal_selesai)->format('d/m/Y') ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Jenis</dt><dd class="font-medium">{{ $serviceReport->jenis_service }}</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $serviceReport->status_service }}</dd></div>
        <div><dt class="text-gray-500">Pelaksana</dt><dd class="font-medium">{{ filled($serviceReport->vendor) ? 'Vendor / Eksternal' : 'Internal' }}</dd></div>
        <div><dt class="text-gray-500">Vendor</dt><dd class="font-medium">{{ $serviceReport->vendor ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Teknisi</dt><dd class="font-medium">{{ $serviceReport->teknisi ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Total Biaya</dt><dd class="font-medium">Rp {{ number_format((float) $serviceReport->total_biaya, 2, ',', '.') }}</dd></div>
        <div><dt class="text-gray-500">Kondisi Setelah Servis</dt><dd class="font-medium">{{ $serviceReport->kondisi_setelah_service ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Rekomendasi</dt><dd class="font-medium">{{ \App\Enums\PemeliharaanRekomendasi::tryFrom((string) $serviceReport->rekomendasi)?->label() ?? ($serviceReport->rekomendasi ?: '-') }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Deskripsi Kerja</dt><dd class="font-medium whitespace-pre-wrap">{{ $serviceReport->deskripsi_kerja ?: '-' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Tindakan</dt><dd class="font-medium whitespace-pre-wrap">{{ $serviceReport->tindakan_yang_dilakukan ?: '-' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Catatan</dt><dd class="font-medium whitespace-pre-wrap">{{ $serviceReport->keterangan ?: ($serviceReport->rekomendasi_catatan ?: '-') }}</dd></div>
        <div class="sm:col-span-2">
            <dt class="text-gray-500">File Laporan</dt>
            <dd class="font-medium">
                @if(!empty($serviceReport->file_laporan))
                    <a href="{{ route('media.show', ['path' => $serviceReport->file_laporan]) }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">Buka File Laporan</a>
                @else
                    -
                @endif
            </dd>
        </div>
    </dl>

    @if($serviceReport->spareparts->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Daftar Spare Part (pengajuan pembelian)</h3>
            <div class="overflow-x-auto border border-gray-200 rounded-md">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-3 py-2">Nama</th>
                            <th class="px-3 py-2">Merk</th>
                            <th class="px-3 py-2">No. Seri</th>
                            <th class="px-3 py-2">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceReport->spareparts as $sp)
                            @php
                                $fotoUrl = $sp->foto_path ? route('media.show', ['path' => $sp->foto_path]) : null;
                            @endphp
                            <tr class="border-t border-gray-100">
                                <td class="px-3 py-2">{{ $sp->nama_sparepart }}</td>
                                <td class="px-3 py-2">{{ $sp->merk ?: '-' }}</td>
                                <td class="px-3 py-2">{{ $sp->nomor_seri ?: '-' }}</td>
                                <td class="px-3 py-2">
                                    @if($fotoUrl)
                                        <button
                                            type="button"
                                            class="sparepart-foto-thumb block w-14 h-14 rounded border border-gray-200 overflow-hidden bg-gray-50 p-0 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                            title="Klik untuk memperbesar"
                                            data-full-src="{{ $fotoUrl }}"
                                        >
                                            <img src="{{ $fotoUrl }}" alt="Foto {{ $sp->nama_sparepart }}" class="w-full h-full object-cover pointer-events-none">
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="sparepart-foto-lightbox" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-label="Preview foto spare part">
            <button type="button" id="sparepart-foto-lightbox-close" class="absolute top-4 right-4 rounded-md bg-white/90 px-3 py-1.5 text-sm font-medium text-gray-800 hover:bg-white">Tutup</button>
            <img id="sparepart-foto-lightbox-img" src="" alt="Preview besar" class="max-h-[90vh] max-w-[95vw] rounded-lg shadow-xl object-contain bg-white">
        </div>
    @endif
</div>

@if($serviceReport->spareparts->isNotEmpty())
@push('scripts')
<script>
(function () {
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
    document.getElementById('sparepart-foto-lightbox-close')?.addEventListener('click', closeLightbox);
    document.getElementById('sparepart-foto-lightbox')?.addEventListener('click', function (e) {
        if (e.target === this) closeLightbox();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox();
    });
    document.addEventListener('click', function (e) {
        const thumb = e.target.closest('.sparepart-foto-thumb');
        if (!thumb) return;
        const src = thumb.getAttribute('data-full-src') || thumb.querySelector('img')?.getAttribute('src');
        if (src) openLightbox(src);
    });
})();
</script>
@endpush
@endif
@endsection
