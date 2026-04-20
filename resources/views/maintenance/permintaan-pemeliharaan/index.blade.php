@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Permintaan Pemeliharaan</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua permintaan pemeliharaan aset</p>
    </div>
    <a 
        href="{{ route('maintenance.permintaan-pemeliharaan.create') }}" 
        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Permintaan
    </a>
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('maintenance.permintaan-pemeliharaan.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
        <div>
            <label for="unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
            <select 
                id="unit_kerja" 
                name="unit_kerja" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Unit Kerja</option>
                @foreach($unitKerjas as $unitKerja)
                    <option value="{{ $unitKerja->id_unit_kerja }}" {{ request('unit_kerja') == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                        {{ $unitKerja->nama_unit_kerja }}
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
                <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="DIAJUKAN" {{ request('status') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                <option value="DISETUJUI" {{ request('status') == 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                <option value="DITOLAK" {{ request('status') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                <option value="DIPROSES" {{ request('status') == 'DIPROSES' ? 'selected' : '' }}>Diproses</option>
                <option value="SELESAI" {{ request('status') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        <div>
            <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
            <select 
                id="jenis" 
                name="jenis" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Jenis</option>
                <option value="RUTIN" {{ request('jenis') == 'RUTIN' ? 'selected' : '' }}>Rutin</option>
                <option value="KALIBRASI" {{ request('jenis') == 'KALIBRASI' ? 'selected' : '' }}>Kalibrasi</option>
                <option value="PERBAIKAN" {{ request('jenis') == 'PERBAIKAN' ? 'selected' : '' }}>Perbaikan</option>
                <option value="PENGGANTIAN_SPAREPART" {{ request('jenis') == 'PENGGANTIAN_SPAREPART' ? 'selected' : '' }}>Penggantian Sparepart</option>
            </select>
        </div>

        <div>
            <label for="prioritas" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
            <select 
                id="prioritas" 
                name="prioritas" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Prioritas</option>
                <option value="RENDAH" {{ request('prioritas') == 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                <option value="SEDANG" {{ request('prioritas') == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                <option value="TINGGI" {{ request('prioritas') == 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                <option value="DARURAT" {{ request('prioritas') == 'DARURAT' ? 'selected' : '' }}>Darurat</option>
            </select>
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
                    placeholder="Cari no permintaan, pemohon, atau nomor register..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
        </div>
    </form>
</div>

<!-- Success/Error Messages -->
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

@if(session('error'))
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<!-- Table Card -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Permintaan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Register</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($permintaans as $permintaan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $permintaan->no_permintaan_pemeliharaan }}</div>
                            <div class="text-xs text-gray-500">{{ $permintaan->tanggal_permintaan->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $permintaan->registerAset->nomor_register ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $permintaan->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $permintaan->pemohon->nama_pegawai ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $jenisColor = match($permintaan->jenis_pemeliharaan) {
                                    'RUTIN' => 'bg-blue-100 text-blue-800',
                                    'KALIBRASI' => 'bg-purple-100 text-purple-800',
                                    'PERBAIKAN' => 'bg-orange-100 text-orange-800',
                                    'PENGGANTIAN_SPAREPART' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $jenisColor }}">
                                {{ $permintaan->jenis_pemeliharaan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $prioritasColor = match($permintaan->prioritas) {
                                    'RENDAH' => 'bg-gray-100 text-gray-800',
                                    'SEDANG' => 'bg-yellow-100 text-yellow-800',
                                    'TINGGI' => 'bg-orange-100 text-orange-800',
                                    'DARURAT' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $prioritasColor }}">
                                {{ $permintaan->prioritas }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($permintaan->status_permintaan) {
                                    'DRAFT' => 'bg-gray-100 text-gray-800',
                                    'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                                    'DISETUJUI' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    'DIPROSES' => 'bg-blue-100 text-blue-800',
                                    'SELESAI' => 'bg-indigo-100 text-indigo-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $permintaan->status_permintaan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a 
                                    href="{{ route('maintenance.permintaan-pemeliharaan.show', $permintaan->id_permintaan_pemeliharaan) }}" 
                                    class="text-blue-600 hover:text-blue-900 transition-colors"
                                >
                                    Detail
                                </a>
                                @if($permintaan->status_permintaan == 'DRAFT')
                                    <a 
                                        href="{{ route('maintenance.permintaan-pemeliharaan.edit', $permintaan->id_permintaan_pemeliharaan) }}" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                    >
                                        Edit
                                    </a>
                                    <form 
                                        action="{{ route('maintenance.permintaan-pemeliharaan.destroy', $permintaan->id_permintaan_pemeliharaan) }}" 
                                        method="POST" 
                                        class="inline" 
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus permintaan ini?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat permintaan pemeliharaan baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($permintaans->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $permintaans->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('#unit_kerja, #status, #jenis, #prioritas').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
@endsection


