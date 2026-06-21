@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Master Sub Kegiatan</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua sub kegiatan yang terdaftar di sistem</p>
    </div>
    <a href="{{ route('master.sub-kegiatan.create') }}" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Sub Kegiatan
    </a>
</div>


<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($subKegiatans instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $subKegiatans->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Sub Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Sub Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subKegiatans as $subKegiatan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$subKegiatans" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $subKegiatan->kode_sub_kegiatan }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $subKegiatan->nama_sub_kegiatan }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $subKegiatan->kegiatan->nama_kegiatan ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('master.sub-kegiatan.show', $subKegiatan->id_sub_kegiatan) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Detail</a>
                                <a href="{{ route('master.sub-kegiatan.edit', $subKegiatan->id_sub_kegiatan) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</a>
                                <form action="{{ route('master.sub-kegiatan.destroy', $subKegiatan->id_sub_kegiatan) }}" method="POST" class="inline" data-confirm="Apakah Anda yakin ingin menghapus data ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan sub kegiatan baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($subKegiatans->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $subKegiatans->links() }}
        </div>
    @endif
</div>
@endsection

