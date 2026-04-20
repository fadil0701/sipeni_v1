@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Paket Pengadaan</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $paket->no_paket }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('procurement.paket-pengadaan.index') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
        <a href="{{ route('procurement.paket-pengadaan.edit', $paket->id_paket) }}" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">Edit</a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Paket</h3>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div><dt class="text-sm font-medium text-gray-500">No. Paket</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->no_paket }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Nama Paket</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->nama_paket }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Sub Kegiatan</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->subKegiatan?->nama_sub_kegiatan ?? '-' }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Program / Kegiatan</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->subKegiatan?->kegiatan?->program?->nama_program ?? '-' }} / {{ $paket->subKegiatan?->kegiatan?->nama_kegiatan ?? '-' }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">RKU</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->rku?->no_rku ?? '-' }} {{ $paket->rku ? '(' . $paket->rku->tahun_anggaran . ')' : '' }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Metode Pengadaan</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->metode_pengadaan }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Nilai Paket</dt><dd class="mt-1 text-sm font-semibold text-gray-900">Rp {{ number_format($paket->nilai_paket ?? 0, 0, ',', '.') }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Status</dt><dd class="mt-1">
                @php $sc = ['DRAFT'=>'bg-gray-100 text-gray-800','DIAJUKAN'=>'bg-yellow-100 text-yellow-800','DIPROSES'=>'bg-blue-100 text-blue-800','SELESAI'=>'bg-green-100 text-green-800','DIBATALKAN'=>'bg-red-100 text-red-800']; $c = $sc[$paket->status_paket] ?? 'bg-gray-100 text-gray-800'; @endphp
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c }}">{{ $paket->status_paket }}</span>
            </dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->tanggal_mulai ? $paket->tanggal_mulai->format('d/m/Y') : '-' }}</dd></div>
            <div><dt class="text-sm font-medium text-gray-500">Tanggal Selesai</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->tanggal_selesai ? $paket->tanggal_selesai->format('d/m/Y') : '-' }}</dd></div>
            @if($paket->deskripsi_paket)
            <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Deskripsi</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->deskripsi_paket }}</dd></div>
            @endif
            @if($paket->keterangan)
            <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Keterangan</dt><dd class="mt-1 text-sm text-gray-900">{{ $paket->keterangan }}</dd></div>
            @endif
        </dl>
    </div>
</div>

@if($paket->kontrak)
<div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <h3 class="px-6 py-4 border-b border-gray-200 bg-gray-50 text-lg font-semibold text-gray-900">Kontrak Terkait</h3>
    <div class="p-6">
        <p class="text-sm text-gray-600">No. Kontrak: <strong>{{ $paket->kontrak->no_kontrak }}</strong></p>
        <p class="text-sm text-gray-600 mt-1">Vendor: {{ $paket->kontrak->nama_vendor }}</p>
        <p class="text-sm text-gray-600 mt-1">Nilai: Rp {{ number_format($paket->kontrak->nilai_kontrak ?? 0, 0, ',', '.') }}</p>
        <p class="text-sm text-gray-500 mt-2">Fitur detail kontrak akan dilengkapi di modul Kontrak/SP/PO.</p>
    </div>
</div>
@endif
@endsection
