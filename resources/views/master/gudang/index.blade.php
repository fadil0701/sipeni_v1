@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Master Gudang</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua gudang yang terdaftar di sistem</p>
    </div>
    <a 
        href="{{ route('master.gudang.create') }}" 
        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Gudang
    </a>
</div>


<!-- Table Card -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($gudangs instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $gudangs->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($gudangs as $gudang)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$gudangs" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $gudang->nama_gudang }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $gudang->unitKerja->nama_unit_kerja ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $jenisColor = $gudang->jenis_gudang == 'PUSAT' ? 'bg-blue-100 text-blue-900' : 'bg-green-100 text-green-900';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $jenisColor }}">
                                {{ $gudang->jenis_gudang }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($gudang->jenis_gudang == 'PUSAT' && $gudang->kategori_gudang)
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $gudang->kategori_gudang == 'ASET' ? 'bg-blue-100 text-blue-900' : ($gudang->kategori_gudang == 'PERSEDIAAN' ? 'bg-green-100 text-green-900' : 'bg-blue-100 text-blue-900') }}">
                                    {{ $gudang->kategori_gudang }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('master.gudang.show', $gudang->id_gudang) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Detail</a>
                                <a href="{{ route('master.gudang.edit', $gudang->id_gudang) }}" class="text-blue-700 hover:text-blue-900 transition-colors">Edit</a>
                                <form action="{{ route('master.gudang.destroy', $gudang->id_gudang) }}" method="POST" class="inline">
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
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan gudang baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($gudangs->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $gudangs->links() }}
        </div>
    @endif
</div>
@endsection

