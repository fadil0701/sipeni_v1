@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kartu Stok</h1>
        <p class="mt-1 text-sm text-gray-600">Riwayat ringkas pergerakan stok per barang dan gudang</p>
        @if(\App\Helpers\StockWarehouseSummaryViewHelper::shouldLimitStockViewsToPersediaanFarmasiForUnit(auth()->user()))
            <p class="mt-2 text-xs text-amber-900 bg-amber-50 border border-amber-200 rounded-md px-2 py-1.5 inline-block">Akun gudang unit: laporan ini hanya memuat baris stok <strong>Persediaan</strong> dan <strong>Farmasi</strong>.</p>
        @endif
        <a href="{{ route('reports.kartu-stok.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Export CSV</a>
    </div>
    @if($showWarehouseSummaryCards ?? false)
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <span class="text-sm font-medium text-gray-700">Tampilan</span>
        <a
            href="{{ request()->fullUrlWithQuery(['tampilan' => 'tabel']) }}"
            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium border transition-colors {{ ($tampilan ?? 'tabel') === 'tabel' ? 'border-blue-600 bg-blue-50 text-blue-800 ring-2 ring-blue-500 ring-offset-1' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}"
        >Tabel</a>
        <a
            href="{{ request()->fullUrlWithQuery(['tampilan' => 'cards']) }}"
            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium border transition-colors {{ ($tampilan ?? 'tabel') === 'cards' ? 'border-blue-600 bg-blue-50 text-blue-800 ring-2 ring-blue-500 ring-offset-1' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}"
        >Ringkasan per gudang</a>
    </div>
    @endif
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('reports.kartu-stok') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <input type="hidden" name="tampilan" value="{{ $tampilan ?? 'tabel' }}">
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

@if($showWarehouseSummaryCards ?? false)
@include('partials.gudang-stock-summary-cards', [
    'routeName' => 'reports.kartu-stok',
    'helpText' => 'Ringkasan mengikuti pencarian barang; filter gudang pada form tidak membatasi total per kartu. Klik kartu untuk membuka tabel rinci hanya untuk gudang tersebut.',
])
@endif

@if(($tampilan ?? 'tabel') === 'tabel')
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" title="Jika 0 di database, ditampilkan saldo awal implisit (Qty akhir − Qty masuk + Qty keluar)">Qty Awal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Akhir</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Update Terakhir</th>
                    @if(\App\Helpers\PermissionHelper::canAccess(auth()->user(), 'reports.kartu-stok.merk-breakdown'))
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    @endif
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
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ number_format((float) ($stock->qty_awal_laporan ?? $stock->qty_awal), 2, ',', '.') }}
                            @if(!empty($stock->qty_awal_terderivasi))
                                <span class="block text-xs text-gray-500 font-normal" title="Disamakan dengan saldo sebelum mutasi tercatat">implisit</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format((float) $stock->qty_masuk, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format((float) $stock->qty_keluar, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format((float) $stock->qty_akhir, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $stock->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ optional($stock->last_updated)->format('d/m/Y H:i') ?? '-' }}</td>
                        @if(\App\Helpers\PermissionHelper::canAccess(auth()->user(), 'reports.kartu-stok.merk-breakdown'))
                            <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                <a
                                    href="{{ route('reports.kartu-stok.merk-breakdown', array_merge(
                                        ['id_data_barang' => $stock->id_data_barang, 'id_gudang' => $stock->id_gudang],
                                        request()->only(['gudang', 'search', 'page', 'tampilan'])
                                    )) }}"
                                    class="inline-flex items-center justify-center p-2 rounded-md text-white bg-red-500 border border-red-600 shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    title="Rincian stok per merk"
                                    aria-label="Rincian stok per merk"
                                >
                                    <span class="sr-only">Rincian stok per merk</span>
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ \App\Helpers\PermissionHelper::canAccess(auth()->user(), 'reports.kartu-stok.merk-breakdown') ? '9' : '8' }}" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data kartu stok.</td>
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
@else
    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-600">
        Tabel disembunyikan pada tampilan ringkasan. Pilih kartu gudang di atas, atau ubah tampilan ke <strong>Tabel</strong>.
    </div>
@endif
@endsection
