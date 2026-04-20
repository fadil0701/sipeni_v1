@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar KIR
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Detail Kartu Inventaris Ruangan</h2>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap KIR</p>
            </div>
            <div class="flex space-x-3">
                @php
                    use App\Helpers\PermissionHelper;
                    $user = auth()->user();
                @endphp
                @if(PermissionHelper::canAccess($user, 'asset.kartu-inventaris-ruangan.edit'))
                <a 
                    href="{{ route('asset.kartu-inventaris-ruangan.edit', $kir->id_kir) }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Informasi KIR -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi KIR</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ruangan</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $kir->ruangan->nama_ruangan ?? '-' }}
                            <span class="text-gray-500 text-xs block">{{ $kir->ruangan->unitKerja->nama_unit_kerja ?? '-' }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Penanggung Jawab</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $kir->penanggungJawab->nama_pegawai ?? '-' }}
                            <span class="text-gray-500 text-xs block">{{ $kir->penanggungJawab->jabatan->nama_jabatan ?? '-' }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Penempatan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $kir->tanggal_penempatan ? $kir->tanggal_penempatan->format('d F Y') : '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informasi Register Aset -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Register Aset</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Register</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $kir->registerAset->nomor_register ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $kir->registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                        </dd>
                    </div>
                    @if($kir->registerAset->inventory->jenis_barang)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $kir->registerAset->inventory->jenis_barang }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Unit Kerja</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $kir->registerAset->unitKerja->nama_unit_kerja ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Kondisi Aset</dt>
                        <dd class="mt-1">
                            @php
                                $kondisiColors = [
                                    'BAIK' => 'bg-green-100 text-green-800',
                                    'RUSAK_RINGAN' => 'bg-yellow-100 text-yellow-800',
                                    'RUSAK_BERAT' => 'bg-red-100 text-red-800',
                                ];
                                $color = $kondisiColors[$kir->registerAset->kondisi_aset] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $color }}">
                                {{ str_replace('_', ' ', $kir->registerAset->kondisi_aset) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Aset</dt>
                        <dd class="mt-1">
                            @if($kir->registerAset->status_aset == 'AKTIF')
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    AKTIF
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    NONAKTIF
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
