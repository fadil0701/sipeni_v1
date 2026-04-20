@extends('layouts.app')

@php
    use App\Helpers\PermissionHelper;
    $user = auth()->user();
@endphp

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.register-aset.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Index
    </a>
</div>

<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }} - Register Aset</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar register aset di {{ $title }}</p>
    </div>
</div>

<!-- Filter berdasarkan jenis gudang -->
@if($isPusat ?? false)
    <!-- Filter untuk Gudang Aset (KIB): Filter Unit Kerja -->
    <div class="mb-4 bg-white p-4 rounded-lg border border-gray-200">
        <form method="GET" action="{{ route('asset.register-aset.unit-kerja.show', ['unit_kerja' => $unitKerjaId]) }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="filter_unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">
                    Filter Unit Kerja
                </label>
                <select 
                    id="filter_unit_kerja" 
                    name="filter_unit_kerja" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua data aset</option>
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->id_unit_kerja }}" {{ request('filter_unit_kerja') == $unit->id_unit_kerja ? 'selected' : '' }}>
                            {{ $unit->nama_unit_kerja }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('filter_unit_kerja'))
            <a href="{{ route('asset.register-aset.unit-kerja.show', ['unit_kerja' => $unitKerjaId]) }}" 
               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Reset
            </a>
            @endif
        </form>
    </div>
@else
    <!-- Filter untuk KIR (Unit Kerja): Filter Ruangan -->
    <div class="mb-4 bg-white p-4 rounded-lg border border-gray-200">
        <form method="GET" action="{{ route('asset.register-aset.unit-kerja.show', ['unit_kerja' => $unitKerjaId]) }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="filter_ruangan" class="block text-sm font-medium text-gray-700 mb-1">
                    Filter Ruangan
                </label>
                <select 
                    id="filter_ruangan" 
                    name="filter_ruangan" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua data KIR</option>
                    @foreach($ruangans as $ruangan)
                        <option value="{{ $ruangan->id_ruangan }}" {{ request('filter_ruangan') == $ruangan->id_ruangan ? 'selected' : '' }}>
                            {{ $ruangan->nama_ruangan }}
                            @if($ruangan->unitKerja)
                                ({{ $ruangan->unitKerja->nama_unit_kerja }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('filter_ruangan'))
            <a href="{{ route('asset.register-aset.unit-kerja.show', ['unit_kerja' => $unitKerjaId]) }}" 
               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Reset
            </a>
            @endif
        </form>
    </div>
@endif


<!-- Table -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Register</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Register</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kondisi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Badge</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventoryItems as $index => $item)
                @php
                    // Ambil RegisterAset untuk InventoryItem ini dari map yang sudah di-preload
                    // Gunakan registerAsetItemMap untuk mapping yang lebih tepat berdasarkan id_item
                    $registerAset = null;
                    if (isset($registerAsetItemMap[$item->id_item])) {
                        // Mapping langsung berdasarkan id_item (lebih tepat)
                        $registerAset = $registerAsetItemMap[$item->id_item];
                    } elseif (isset($registerAsetsMap[$item->id_inventory])) {
                        // Fallback: ambil RegisterAset pertama yang sesuai dengan unit kerja
                        $unitKerjaId = isset($gudangUnitForView) && $gudangUnitForView->unitKerja ? $gudangUnitForView->unitKerja->id_unit_kerja : null;
                        if ($unitKerjaId) {
                            $registerAset = $registerAsetsMap[$item->id_inventory]->firstWhere('id_unit_kerja', $unitKerjaId);
                        } else {
                            // Untuk gudang pusat, ambil RegisterAset pertama jika ada
                            $registerAset = $registerAsetsMap[$item->id_inventory]->first();
                        }
                    }
                    
                    // Tentukan badge KIB/KIR berdasarkan RegisterAset dan lokasi
                    $isKIB = false;
                    $isKIR = false;
                    
                    // Untuk gudang pusat: semua aset adalah KIB (baik yang sudah ter-register maupun belum)
                    if ($isPusat ?? false) {
                        $isKIB = true; // Semua aset di gudang pusat adalah KIB
                    } elseif ($registerAset) {
                        // Untuk gudang unit: badge berdasarkan RegisterAset
                        if ($registerAset->id_ruangan) {
                            $isKIR = true; // Punya ruangan = KIR
                        } else {
                            $isKIB = true; // Tidak punya ruangan = KIB
                        }
                    } else {
                        // Fallback: jika tidak ada RegisterAset di gudang unit
                        $currentGudang = $item->gudang ?? null;
                        if ($currentGudang && $currentGudang->jenis_gudang == 'UNIT') {
                            // Di gudang unit tapi belum ter-register = tidak tampil (hanya KIR yang ditampilkan)
                            $isKIR = false;
                        } elseif ($item->id_ruangan) {
                            $isKIR = true;
                        }
                    }
                @endphp
                <tr class="hover:bg-gray-50">
                    <!-- Kode Register (selalu ada, dari InventoryItem) -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $item->kode_register ?? '-' }}
                    </td>
                    
                    <!-- Nomor Register / Status Belum Register (digabung dalam 1 kolom) -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($registerAset && $registerAset->nomor_register)
                            <span class="font-medium text-blue-600">{{ $registerAset->nomor_register }}</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800" title="Belum di-register">
                                Belum Register
                            </span>
                        @endif
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->inventory->dataBarang->nama_barang ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->inventory->merk ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->inventory->jenis_barang ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($registerAset && $registerAset->unitKerja)
                            {{-- Jika sudah di-register ke unit kerja, tampilkan gudang unit kerja --}}
                            @php
                                // Cari gudang unit untuk unit kerja ini
                                $gudangUnitKerja = null;
                                if ($registerAset->unitKerja && $registerAset->unitKerja->relationLoaded('gudang')) {
                                    $gudangUnitKerja = $registerAset->unitKerja->gudang->where('jenis_gudang', 'UNIT')
                                        ->where('kategori_gudang', 'ASET')
                                        ->first();
                                }
                                if (!$gudangUnitKerja) {
                                    $gudangUnitKerja = \App\Models\MasterGudang::where('id_unit_kerja', $registerAset->unitKerja->id_unit_kerja)
                                        ->where('jenis_gudang', 'UNIT')
                                        ->where('kategori_gudang', 'ASET')
                                        ->first();
                                }
                            @endphp
                            @if($gudangUnitKerja)
                                {{ $gudangUnitKerja->nama_gudang }}
                            @else
                                {{ $registerAset->unitKerja->nama_unit_kerja ?? '-' }}
                            @endif
                        @elseif(isset($gudangUnitForView) && $gudangUnitForView)
                            {{-- Fallback: gunakan gudang unit dari context --}}
                            {{ $gudangUnitForView->nama_gudang ?? '-' }}
                        @elseif($item->gudang)
                            {{ $item->gudang->nama_gudang ?? '-' }}
                        @elseif($item->inventory && $item->inventory->gudang)
                            {{ $item->inventory->gudang->nama_gudang ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($registerAset && $registerAset->id_ruangan)
                            {{ $registerAset->ruangan?->nama_ruangan ?? '-' }}
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $kondisiColors = [
                                'BAIK' => 'bg-green-100 text-green-800',
                                'RUSAK_RINGAN' => 'bg-yellow-100 text-yellow-800',
                                'RUSAK_BERAT' => 'bg-red-100 text-red-800',
                            ];
                            $kondisi = $item->kondisi_item ?? 'BAIK';
                            $color = $kondisiColors[$kondisi] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                            {{ str_replace('_', ' ', $kondisi) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $status = $item->status_item ?? 'AKTIF';
                        @endphp
                        @if($status == 'AKTIF')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                AKTIF
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $status }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($isKIB)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                KIB
                            </span>
                        @elseif($isKIR)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                KIR
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                -
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            @if($registerAset)
                                <a 
                                    href="{{ route('asset.register-aset.show', $registerAset->id_register_aset) }}" 
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Detail
                                </a>
                                @if(PermissionHelper::canAccess($user, 'asset.register-aset.edit'))
                                <a 
                                    href="{{ route('asset.register-aset.edit', $registerAset->id_register_aset) }}" 
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </a>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                        Tidak ada data aset.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($inventoryItems->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $inventoryItems->links() }}
    </div>
    @endif
</div>
@endsection

