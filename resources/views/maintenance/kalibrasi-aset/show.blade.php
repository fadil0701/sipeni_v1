@extends('layouts.app')

@section('content')
<div class="mb-4"><a href="{{ route('maintenance.kalibrasi-aset.index') }}" class="text-blue-600 hover:text-blue-900">Kembali ke daftar</a></div>
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-xl font-semibold">Detail Kalibrasi Aset</h2>
            <p class="text-sm text-gray-600">{{ $kalibrasi->no_kalibrasi }}</p>
        </div>
        <a href="{{ route('maintenance.kalibrasi-aset.edit', $kalibrasi->id_kalibrasi) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md">Edit</a>
    </div>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 text-sm">
        <div><dt class="text-gray-500">Nomor Register</dt><dd class="font-medium">{{ $kalibrasi->registerAset->nomor_register ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Nama Barang</dt><dd class="font-medium">{{ $kalibrasi->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Kalibrasi</dt><dd class="font-medium">{{ optional($kalibrasi->tanggal_kalibrasi)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Berlaku</dt><dd class="font-medium">{{ optional($kalibrasi->tanggal_berlaku)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Kadaluarsa</dt><dd class="font-medium">{{ optional($kalibrasi->tanggal_kadaluarsa)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $kalibrasi->status_kalibrasi }}</dd></div>
        <div><dt class="text-gray-500">Lembaga</dt><dd class="font-medium">{{ $kalibrasi->lembaga_kalibrasi ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">No Sertifikat</dt><dd class="font-medium">{{ $kalibrasi->no_sertifikat ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Biaya</dt><dd class="font-medium">Rp {{ number_format((float) $kalibrasi->biaya_kalibrasi, 2, ',', '.') }}</dd></div>
        <div><dt class="text-gray-500">Pemohon</dt><dd class="font-medium">{{ $kalibrasi->permintaanPemeliharaan->pemohon->nama_pegawai ?? '-' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Keterangan</dt><dd class="font-medium whitespace-pre-wrap">{{ $kalibrasi->keterangan ?: '-' }}</dd></div>
        <div class="sm:col-span-2">
            <dt class="text-gray-500">File Sertifikat</dt>
            <dd class="font-medium">
                @if(!empty($kalibrasi->file_sertifikat))
                    @php
                        $fileUrl = asset('storage/' . $kalibrasi->file_sertifikat);
                        $fileExt = strtolower(pathinfo($kalibrasi->file_sertifikat, PATHINFO_EXTENSION));
                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                        $isDocLike = in_array($fileExt, ['pdf', 'doc', 'docx'], true);
                    @endphp
                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">Buka File Sertifikat</a>
                    @if($isImage)
                        <div class="mt-2">
                            <img src="{{ $fileUrl }}" alt="File sertifikat kalibrasi" class="max-h-72 rounded border border-gray-200">
                        </div>
                    @elseif($isDocLike)
                        <p class="mt-1 text-xs text-gray-500">File non-gambar ditampilkan sebagai link.</p>
                    @endif
                @else
                    -
                @endif
            </dd>
        </div>
    </dl>
</div>
@endsection
