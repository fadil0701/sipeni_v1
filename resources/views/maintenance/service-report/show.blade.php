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
        <div><dt class="text-gray-500">Tanggal Servis</dt><dd class="font-medium">{{ optional($serviceReport->tanggal_service)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Selesai</dt><dd class="font-medium">{{ optional($serviceReport->tanggal_selesai)->format('d/m/Y') ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Jenis</dt><dd class="font-medium">{{ $serviceReport->jenis_service }}</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $serviceReport->status_service }}</dd></div>
        <div><dt class="text-gray-500">Vendor</dt><dd class="font-medium">{{ $serviceReport->vendor ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Teknisi</dt><dd class="font-medium">{{ $serviceReport->teknisi ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Total Biaya</dt><dd class="font-medium">Rp {{ number_format((float) $serviceReport->total_biaya, 2, ',', '.') }}</dd></div>
        <div><dt class="text-gray-500">Kondisi Setelah Servis</dt><dd class="font-medium">{{ $serviceReport->kondisi_setelah_service ?: '-' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Deskripsi Kerja</dt><dd class="font-medium whitespace-pre-wrap">{{ $serviceReport->deskripsi_kerja ?: '-' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Tindakan</dt><dd class="font-medium whitespace-pre-wrap">{{ $serviceReport->tindakan_yang_dilakukan ?: '-' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Sparepart</dt><dd class="font-medium whitespace-pre-wrap">{{ $serviceReport->sparepart_yang_diganti ?: '-' }}</dd></div>
        <div class="sm:col-span-2">
            <dt class="text-gray-500">File Laporan</dt>
            <dd class="font-medium">
                @if(!empty($serviceReport->file_laporan))
                    @php
                        $fileUrl = asset('storage/' . $serviceReport->file_laporan);
                        $fileExt = strtolower(pathinfo($serviceReport->file_laporan, PATHINFO_EXTENSION));
                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                        $isDocLike = in_array($fileExt, ['pdf', 'doc', 'docx'], true);
                    @endphp
                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">Buka File Laporan</a>
                    @if($isImage)
                        <div class="mt-2">
                            <img src="{{ $fileUrl }}" alt="File laporan service" class="max-h-72 rounded border border-gray-200">
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
