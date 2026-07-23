@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Data Distribusi Barang (SBBK)</h1>
        <p class="mt-1 text-sm text-gray-600">Monitoring transaksi distribusi barang antar gudang</p>
    </div>
    @if(\App\Helpers\PermissionHelper::canAccess(auth()->user(), 'transaction.distribusi.create'))
        <a
            href="{{ route('transaction.distribusi.create') }}"
            class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat SBBK / Distribusi
        </a>
    @endif
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('transaction.distribusi.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <div>
            <label for="gudang" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
            <select 
                id="gudang" 
                name="gudang" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Gudang</option>
                @foreach($gudangs as $gudang)
                    <option value="{{ $gudang->id_gudang }}" {{ request('gudang') == $gudang->id_gudang ? 'selected' : '' }}>
                        {{ $gudang->nama_gudang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
                id="status" 
                name="status" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                <option value="dikirim" {{ request('status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        <div>
            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input 
                type="date" 
                id="tanggal_mulai" 
                name="tanggal_mulai" 
                value="{{ request('tanggal_mulai') }}"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>

        <div>
            <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input 
                type="date" 
                id="tanggal_akhir" 
                name="tanggal_akhir" 
                value="{{ request('tanggal_akhir') }}"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>

        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari no SBBK atau permintaan..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
        </div>

        <div class="flex items-end">
            <button 
                type="submit" 
                class="w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($distribusis instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $distribusis->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No SBBK</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Permintaan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Asal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Tujuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="no-sort px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($distribusis as $distribusi)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$distribusis" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $distribusi->no_sbbk }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $distribusi->permintaan->no_permintaan ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $distribusi->tanggal_distribusi->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $distribusi->gudangAsal->nama_gudang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $distribusi->gudangTujuan->nama_gudang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusValue = $distribusi->status_distribusi instanceof \App\Enums\DistribusiStatus
                                    ? $distribusi->status_distribusi->value
                                    : $distribusi->status_distribusi;

                                $statusColor = \App\Support\UiColor::badgeForStatus($statusValue);
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $statusValue }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="inline-flex items-center justify-end gap-1">
                                <x-ui.btn
                                    action="detail"
                                    size="icon"
                                    soft
                                    href="{{ route('transaction.distribusi.show', $distribusi->id_distribusi) }}"
                                    title="Detail"
                                />

                                @if($statusValue === 'draft')
                                    <x-ui.btn
                                        action="proses"
                                        size="sm"
                                        soft
                                        href="{{ route('transaction.distribusi.edit', ['id' => $distribusi->id_distribusi, 'intent' => 'proses']) }}"
                                        title="Proses — isi pegawai pengirim"
                                    />
                                @endif

                                @if(in_array($statusValue, ['draft', 'diproses'], true))
                                    <x-ui.btn
                                        action="edit"
                                        size="icon"
                                        soft
                                        href="{{ route('transaction.distribusi.edit', $distribusi->id_distribusi) }}"
                                        title="Edit"
                                    />
                                @endif

                                @if($statusValue === 'diproses')
                                    @if(!$distribusi->id_pegawai_pengirim)
                                        <a
                                            href="{{ route('transaction.distribusi.edit', ['id' => $distribusi->id_distribusi, 'intent' => 'proses']) }}"
                                            class="inline-flex items-center justify-center p-2 rounded-md text-blue-800 bg-blue-100 hover:bg-blue-200 transition-colors"
                                            title="Isi pegawai pengirim"
                                            aria-label="Isi pegawai pengirim"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </a>
                                    @else
                                    <form method="POST" action="{{ route('transaction.distribusi.kirim', $distribusi->id_distribusi) }}" class="inline" data-confirm="Kirim distribusi ini sekarang?">
                                        @csrf
                                        <input type="hidden" name="kirim_from" value="index">
                                        <button 
                                            type="submit"
                                            class="inline-flex items-center justify-center p-2 rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition-colors"
                                            title="Kirim"
                                            aria-label="Kirim"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Belum ada transaksi distribusi.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($distribusis->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $distribusis->links() }}
        </div>
    @endif
</div>

@if(session('kirim_popup'))
@push('scripts')
<script>
    alert(@json(session('kirim_popup')));
</script>
@endpush
@endif
@endsection

