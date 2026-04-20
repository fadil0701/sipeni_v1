@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Adjustment</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar dan riwayat penyesuaian stock barang</p>
    </div>
    @php
        use App\Helpers\PermissionHelper;
        $user = auth()->user();
    @endphp
    @if(PermissionHelper::canAccess($user, 'inventory.stock-adjustment.create'))
    <a 
        href="{{ route('inventory.stock-adjustment.create') }}" 
        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Adjustment
    </a>
    @endif
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('inventory.stock-adjustment.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
                id="status" 
                name="status" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Status</option>
                <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="DIAJUKAN" {{ request('status') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                <option value="DISETUJUI" {{ request('status') == 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                <option value="DITOLAK" {{ request('status') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div>
            <label for="id_data_barang" class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
            <select 
                id="id_data_barang" 
                name="id_data_barang" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Barang</option>
                @foreach($barangs ?? [] as $barang)
                    <option value="{{ $barang->id_data_barang }}" {{ request('id_data_barang') == $barang->id_data_barang ? 'selected' : '' }}>
                        {{ $barang->kode_data_barang ?? '' }} - {{ $barang->nama_barang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="id_gudang" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
            <select 
                id="id_gudang" 
                name="id_gudang" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Gudang</option>
                @foreach($gudangs as $gudang)
                    <option value="{{ $gudang->id_gudang }}" {{ request('id_gudang') == $gudang->id_gudang ? 'selected' : '' }}>
                        {{ $gudang->nama_gudang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tanggal_dari" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
            <input 
                type="date" 
                id="tanggal_dari" 
                name="tanggal_dari" 
                value="{{ request('tanggal_dari') }}"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>

        <div>
            <label for="tanggal_sampai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
            <input 
                type="date" 
                id="tanggal_sampai" 
                name="tanggal_sampai" 
                value="{{ request('tanggal_sampai') }}"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>

        <div class="flex items-end gap-2">
            <button 
                type="submit" 
                class="flex-1 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filter
            </button>
            <a 
                href="{{ route('inventory.stock-adjustment.index') }}" 
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sebelum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sesudah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selisih</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($adjustments as $index => $adjustment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $adjustments->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $adjustment->tanggal_adjustment ? $adjustment->tanggal_adjustment->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $adjustment->dataBarang->nama_barang ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $adjustment->gudang->nama_gudang ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ number_format($adjustment->qty_sebelum, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                        {{ number_format($adjustment->qty_sesudah, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($adjustment->qty_selisih > 0)
                            <span class="text-green-600 font-semibold">+{{ number_format($adjustment->qty_selisih, 2) }}</span>
                        @elseif($adjustment->qty_selisih < 0)
                            <span class="text-red-600 font-semibold">{{ number_format($adjustment->qty_selisih, 2) }}</span>
                        @else
                            <span class="text-gray-500">0</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $statusColors = [
                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                                'DISETUJUI' => 'bg-green-100 text-green-800',
                                'DITOLAK' => 'bg-red-100 text-red-800',
                            ];
                            $color = $statusColors[$adjustment->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                            {{ $adjustment->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a 
                            href="{{ route('inventory.stock-adjustment.show', $adjustment->id_adjustment) }}" 
                            class="text-blue-600 hover:text-blue-900"
                        >
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                        Tidak ada data stock adjustment
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($adjustments->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $adjustments->links() }}
    </div>
    @endif
</div>
@endsection
