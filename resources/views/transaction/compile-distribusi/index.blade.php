@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Data SBBK</h1>
        <p class="mt-1 text-sm text-gray-600">Monitoring dokumen SBBK yang sudah terbentuk</p>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('transaction.compile-distribusi.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <div>
            <label for="gudang" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
            <select id="gudang" name="gudang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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
            <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                <option value="dikirim" {{ request('status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        <div>
            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>

        <div>
            <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>

        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari no SBBK atau permintaan..." class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>
</div>

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
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($distribusis as $distribusi)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$distribusis" />
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $distribusi->no_sbbk }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $distribusi->permintaan->no_permintaan ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $distribusi->tanggal_distribusi->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $distribusi->gudangAsal->nama_gudang ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $distribusi->gudangTujuan->nama_gudang ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($distribusi->status_distribusi) {
                                    'dikirim' => 'bg-blue-100 text-blue-800',
                                    'selesai' => 'bg-green-100 text-green-800',
                                    'diproses' => 'bg-indigo-100 text-indigo-800',
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">{{ $distribusi->status_distribusi }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('transaction.distribusi.show', $distribusi->id_distribusi) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">Belum ada dokumen SBBK.</td>
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
@endsection

