@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kartu Inventaris Ruangan (KIR)</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar aset yang ditempatkan di ruangan</p>
    </div>
    @php
        use App\Helpers\PermissionHelper;
        $user = auth()->user();
    @endphp
    @if(PermissionHelper::canAccess($user, 'asset.kartu-inventaris-ruangan.create'))
    <a 
        href="{{ route('asset.kartu-inventaris-ruangan.create') }}" 
        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah KIR
    </a>
    @endif
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="id_ruangan" class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
            <select 
                id="id_ruangan" 
                name="id_ruangan" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Ruangan</option>
                @foreach($ruangans as $ruangan)
                    <option value="{{ $ruangan->id_ruangan }}" {{ request('id_ruangan') == $ruangan->id_ruangan ? 'selected' : '' }}>
                        {{ $ruangan->nama_ruangan }} ({{ $ruangan->unitKerja->nama_unit_kerja ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 flex items-end">
            <button 
                type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filter
            </button>
            <a 
                href="{{ route('asset.kartu-inventaris-ruangan.index') }}" 
                class="ml-2 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Register</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penanggung Jawab</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Penempatan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventoryItems as $index => $item)
                @php
                    // Ambil RegisterAset yang memiliki ruangan dari inventory ini
                    $registerAset = $item->inventory->registerAset->firstWhere('id_ruangan', '!=', null);
                    $kir = null;
                    $penanggungJawab = null;
                    $tanggalPenempatan = null;
                    $ruangan = null;
                    
                    if ($registerAset) {
                        $kir = $registerAset->kartuInventarisRuangan->first();
                        $ruangan = $registerAset->ruangan;
                        
                        if ($kir) {
                            $penanggungJawab = $kir->penanggungJawab;
                            $tanggalPenempatan = $kir->tanggal_penempatan;
                        }
                    }
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $inventoryItems->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $registerAset->nomor_register ?? $item->kode_register ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->inventory->dataBarang->nama_barang ?? '-' }}
                        @if($item->no_seri)
                            <span class="text-gray-500 text-xs block">No. Seri: {{ $item->no_seri }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->inventory->jenis_barang ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($ruangan)
                            {{ $ruangan->nama_ruangan ?? '-' }}
                            <span class="text-gray-500 text-xs block">{{ $ruangan->unitKerja->nama_unit_kerja ?? '-' }}</span>
                        @else
                            <span class="text-gray-400">Belum ditempatkan</span>
                            @if($item->gudang)
                                <span class="text-gray-500 text-xs block">Gudang: {{ $item->gudang->nama_gudang ?? '-' }}</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $penanggungJawab->nama_pegawai ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $tanggalPenempatan ? $tanggalPenempatan->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($kir)
                            <a 
                                href="{{ route('asset.kartu-inventaris-ruangan.show', $kir->id_kir) }}" 
                                class="text-blue-600 hover:text-blue-900 mr-3"
                            >
                                Detail
                            </a>
                            @if(PermissionHelper::canAccess($user, 'asset.kartu-inventaris-ruangan.edit'))
                            <a 
                                href="{{ route('asset.kartu-inventaris-ruangan.edit', $kir->id_kir) }}" 
                                class="text-indigo-600 hover:text-indigo-900"
                            >
                                Edit
                            </a>
                            @endif
                        @elseif($registerAset && $registerAset->id_ruangan)
                            <span class="text-gray-400 text-xs">Belum ada KIR</span>
                        @else
                            <span class="text-gray-400 text-xs">Belum ditempatkan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                        Tidak ada data aset
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($inventoryItems->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $inventoryItems->links() }}
    </div>
    @endif
</div>
@endsection
