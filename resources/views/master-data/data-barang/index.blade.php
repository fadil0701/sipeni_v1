@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Master Data Barang</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua data barang yang terdaftar di sistem</p>
    </div>
    <a href="{{ route('master-data.data-barang.create') }}" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Data Barang
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

<x-index.filter-toolbar
    :action="route('master-data.data-barang.index')"
    search-placeholder="Cari nama barang, kode, subjenis, atau satuan..."
    button-text="Terapkan"
>
    <x-slot:filters>
        <div class="w-full min-w-0 lg:w-auto lg:min-w-[18rem]">
            <label for="id_subjenis_barang" class="block text-sm font-medium text-gray-700 mb-1">Subjenis</label>
            <select
                id="id_subjenis_barang"
                name="id_subjenis_barang"
                data-searchable="true"
                class="select-searchable block w-full rounded-md border border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Semua Subjenis</option>
                @foreach(($subjenisBarangs ?? collect()) as $subjenisBarang)
                    <option value="{{ $subjenisBarang->id_subjenis_barang }}" {{ (string) request('id_subjenis_barang') === (string) $subjenisBarang->id_subjenis_barang ? 'selected' : '' }}>
                        {{ $subjenisBarang->nama_subjenis_barang }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full min-w-0 lg:w-auto lg:min-w-[14rem]">
            <label for="id_satuan" class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
            <select
                id="id_satuan"
                name="id_satuan"
                data-searchable="true"
                class="select-searchable block w-full rounded-md border border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Semua Satuan</option>
                @foreach(($satuans ?? collect()) as $satuan)
                    <option value="{{ $satuan->id_satuan }}" {{ (string) request('id_satuan') === (string) $satuan->id_satuan ? 'selected' : '' }}>
                        {{ $satuan->nama_satuan }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-slot:filters>
</x-index.filter-toolbar>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($dataBarangs instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $dataBarangs->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subjenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($dataBarangs as $dataBarang)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$dataBarangs" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $dataBarang->kode_data_barang }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $dataBarang->nama_barang }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $dataBarang->subjenisBarang->nama_subjenis_barang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $dataBarang->satuan->nama_satuan ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('master-data.data-barang.show', $dataBarang->id_data_barang) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Detail</a>
                                <a href="{{ route('master-data.data-barang.edit', $dataBarang->id_data_barang) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</a>
                                <form action="{{ route('master-data.data-barang.destroy', $dataBarang->id_data_barang) }}" method="POST" class="inline" data-confirm="Apakah Anda yakin ingin menghapus data ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan data barang baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($dataBarangs->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $dataBarangs->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function initFilterSearchableSelects() {
            if (typeof window.initChoicesForSelect !== 'function') {
                return false;
            }

            var initializedAny = false;
            ['id_subjenis_barang', 'id_satuan'].forEach(function (fieldId) {
                var el = document.getElementById(fieldId);
                if (!el || el.choicesInstance) return;
                window.initChoicesForSelect(el, 0);
                initializedAny = initializedAny || !!el.choicesInstance;
            });

            return initializedAny;
        }

        function bootstrapWithRetry() {
            if (initFilterSearchableSelects()) return;
            var retries = 0;
            var maxRetries = 20;
            var timer = setInterval(function () {
                retries++;
                if (initFilterSearchableSelects() || retries >= maxRetries) {
                    clearInterval(timer);
                }
            }, 150);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootstrapWithRetry, { once: true });
        } else {
            bootstrapWithRetry();
        }
        window.addEventListener('load', bootstrapWithRetry, { once: true });
    })();
</script>
@endpush

