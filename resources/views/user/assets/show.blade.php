@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('user.assets') }}" class="text-blue-600 hover:text-blue-900">← Kembali ke Daftar Aset</a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Aset</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Aset</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->inventory->dataBarang->nama_barang ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Kode Aset</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->kode_register ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Lokasi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->ruangan->nama_ruangan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        @php
                            $status = $asset->kondisi_item ?? 'N/A';
                            $color = match($status) {
                                'BAIK' => 'bg-green-100 text-green-800',
                                'RUSAK_RINGAN' => 'bg-yellow-100 text-yellow-800',
                                'RUSAK_BERAT' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                            {{ $status === 'BAIK' ? 'Baik' : $status }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection

