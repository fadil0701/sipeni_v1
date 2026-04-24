@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Kartu Stok</h1>
    <p class="mt-1 text-sm text-gray-600">Riwayat ringkas pergerakan stok per barang dan gudang</p>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('reports.kartu-stok') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="gudang" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
            <select id="gudang" name="gudang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                <option value="">Semua Gudang</option>
                @foreach($gudangs as $gudang)
                    <option value="{{ $gudang->id_gudang }}" {{ request('gudang') == $gudang->id_gudang ? 'selected' : '' }}>
                        {{ $gudang->nama_gudang }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Barang</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Nama / kode barang"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Filter</button>
            <a href="{{ route('reports.kartu-stok') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm hover:bg-gray-200">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Awal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Akhir</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Update Terakhir</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($stocks as $stock)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="font-medium">{{ $stock->dataBarang->nama_barang ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $stock->dataBarang->kode_data_barang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $stock->gudang->nama_gudang ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format((float) $stock->qty_awal, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format((float) $stock->qty_masuk, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format((float) $stock->qty_keluar, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format((float) $stock->qty_akhir, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $stock->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ optional($stock->last_updated)->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data kartu stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stocks->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            {{ $stocks->links() }}
        </div>
    @endif
</div>
@endsection
