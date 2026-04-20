@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.mutasi-aset.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Mutasi
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Detail Mutasi Aset</h2>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap mutasi aset</p>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Informasi Mutasi -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Mutasi</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Mutasi</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $mutasiAset->tanggal_mutasi ? $mutasiAset->tanggal_mutasi->format('d F Y') : '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ruangan Asal</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $mutasiAset->ruanganAsal->nama_ruangan ?? '-' }}
                            <span class="text-gray-500 text-xs block">{{ $mutasiAset->ruanganAsal->unitKerja->nama_unit_kerja ?? '-' }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ruangan Tujuan</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $mutasiAset->ruanganTujuan->nama_ruangan ?? '-' }}
                            <span class="text-gray-500 text-xs block">{{ $mutasiAset->ruanganTujuan->unitKerja->nama_unit_kerja ?? '-' }}</span>
                        </dd>
                    </div>
                    @if($mutasiAset->keterangan)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $mutasiAset->keterangan }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Informasi Register Aset -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Register Aset</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Register</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $mutasiAset->registerAset->nomor_register ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $mutasiAset->registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                        </dd>
                    </div>
                    @if($mutasiAset->registerAset->inventory->jenis_barang)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $mutasiAset->registerAset->inventory->jenis_barang }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Unit Kerja</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $mutasiAset->registerAset->unitKerja->nama_unit_kerja ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
