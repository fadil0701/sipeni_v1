@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.register-aset.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Register Aset
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Detail Register Aset</h2>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap register aset</p>
            </div>
            <div class="flex space-x-3">
                @php
                    use App\Helpers\PermissionHelper;
                    $user = auth()->user();
                @endphp
                @if(PermissionHelper::canAccess($user, 'asset.register-aset.edit'))
                <a 
                    href="{{ route('asset.register-aset.edit', $registerAset->id_register_aset) }}" 
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
            <!-- Informasi Register Aset -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Register Aset</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Register</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registerAset->nomor_register ?? '-' }}</dd>
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
                                $color = $kondisiColors[$registerAset->kondisi_aset] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $color }}">
                                {{ str_replace('_', ' ', $registerAset->kondisi_aset) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Aset</dt>
                        <dd class="mt-1">
                            @if($registerAset->status_aset == 'AKTIF')
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
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Perolehan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->tanggal_perolehan ? $registerAset->tanggal_perolehan->format('d F Y') : '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Gudang Unit</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->unitKerja->nama_unit_kerja ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informasi Barang -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Barang</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Kode Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->inventory->dataBarang->kode_data_barang ?? '-' }}
                        </dd>
                    </div>
                    @if($registerAset->inventory->jenis_barang)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->jenis_barang }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Merk</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->inventory->merk ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipe</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->inventory->tipe ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">No. Seri</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->inventory->no_seri ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty (Jumlah ter-register)</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            1
                            @if($registerAset->inventory && $registerAset->inventory->satuan)
                                {{ $registerAset->inventory->satuan->nama_satuan }}
                            @else
                                Unit
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Harga Satuan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            Rp {{ number_format($registerAset->inventory->harga_satuan ?? 0, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Harga</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($registerAset->inventory->harga_satuan ?? 0, 0, ',', '.') }}
                        </dd>
                    </div>
                    @if($registerAset->inventory->spesifikasi)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Spesifikasi</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->inventory->spesifikasi }}
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Gudang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $registerAset->inventory->gudang->nama_gudang ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Informasi Tambahan -->
        @if($registerAset->inventory->tahun_produksi || $registerAset->inventory->tahun_anggaran)
        <div class="mt-6 bg-gray-50 rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @if($registerAset->inventory->tahun_produksi)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tahun Produksi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->tahun_produksi }}</dd>
                </div>
                @endif
                @if($registerAset->inventory->tahun_anggaran)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tahun Anggaran</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->tahun_anggaran }}</dd>
                </div>
                @endif
                @if($registerAset->inventory->sumberAnggaran)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sumber Anggaran</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->sumberAnggaran->nama_anggaran ?? '-' }}</dd>
                </div>
                @endif
            </dl>
        </div>
        @endif

        <!-- Keterkaitan Data -->
        <div class="mt-6 bg-blue-50 rounded-lg p-6 border border-blue-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Keterkaitan Data</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Kartu Inventaris Ruangan</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $registerAset->kartuInventarisRuangan->count() ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mutasi Aset</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $registerAset->mutasiAset->count() ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pemeliharaan</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $registerAset->permintaanPemeliharaan->count() ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Jadwal Maintenance</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $registerAset->jadwalMaintenance->count() ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

