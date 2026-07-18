@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Master Kegiatan</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua kegiatan yang terdaftar di sistem</p>
    </div>
    <a href="{{ route('master.kegiatan.create') }}" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Kegiatan
    </a>
</div>


<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($kegiatans instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $kegiatans->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($kegiatans as $kegiatan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$kegiatans" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-900">{{ $kegiatan->kode_kegiatan ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $kegiatan->nama_kegiatan }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($kegiatan->program)
                                    <span class="font-mono text-gray-600">{{ $kegiatan->program->kode_program ?? '—' }}</span>
                                    <span class="mx-1">—</span>
                                    <span>{{ $kegiatan->program->nama_program }}</span>
                                @else
                                    —
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('master.kegiatan.show', $kegiatan->id_kegiatan) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Detail</a>
                                <a href="{{ route('master.kegiatan.edit', $kegiatan->id_kegiatan) }}" class="text-blue-700 hover:text-blue-900 transition-colors">Edit</a>
                                <form action="{{ route('master.kegiatan.destroy', $kegiatan->id_kegiatan) }}" method="POST" class="inline" data-confirm="Apakah Anda yakin ingin menghapus data ini?">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan kegiatan baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($kegiatans->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $kegiatans->links() }}
        </div>
    @endif
</div>
@endsection

