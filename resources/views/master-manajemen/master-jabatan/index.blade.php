@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Master Jabatan</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua jabatan yang terdaftar di sistem</p>
    </div>
    <a href="{{ route('master-manajemen.master-jabatan.create') }}" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Jabatan
    </a>
</div>



<x-index.filter-toolbar
    :action="route('master-manajemen.master-jabatan.index')"
    form-id="filterForm"
    search-placeholder="Nama Jabatan, Deskripsi..."
>
    <x-slot:before>
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}" id="hidden_per_page">
    </x-slot:before>
    <x-slot:filters></x-slot:filters>
</x-index.filter-toolbar>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($jabatans instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $jabatans->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pegawai</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($jabatans as $jabatan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$jabatans" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $jabatan->urutan }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $jabatan->nama_jabatan }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $jabatan->deskripsi ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $jabatan->pegawai_count ?? 0 }} pegawai</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('master-manajemen.master-jabatan.show', $jabatan->id_jabatan) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Detail</a>
                                <a href="{{ route('master-manajemen.master-jabatan.edit', $jabatan->id_jabatan) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</a>
                                <form action="{{ route('master-manajemen.master-jabatan.destroy', $jabatan->id_jabatan) }}" method="POST" class="inline" data-confirm="Apakah Anda yakin ingin menghapus jabatan ini?">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Belum ada jabatan yang terdaftar.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <label for="per_page" class="text-sm text-gray-700">Tampilkan:</label>
            <select 
                id="per_page" 
                name="per_page" 
                onchange="updatePerPage(this.value)"
                class="px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-gray-700">baris</span>
        </div>
        
        @if($jabatans->hasPages())
            <div>
                {{ $jabatans->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>


@push('scripts')
<script>
    function updatePerPage(value) {
        const hiddenInput = document.getElementById('hidden_per_page');
        if (hiddenInput) {
            hiddenInput.value = value;
        }
        // Submit form dengan per_page baru
        const form = document.getElementById('filterForm');
        if (form) {
            form.submit();
        } else {
            // Jika tidak ada form, redirect dengan per_page
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            window.location.href = url.toString();
        }
    }
    
</script>
@endpush
@endsection

