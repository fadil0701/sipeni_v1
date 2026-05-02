@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Penerimaan Barang</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar penerimaan dari distribusi (SBBK). Setelah pengiriman, verifikasi barang di halaman detail.</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('transaction.penerimaan-barang.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
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
                <option value="MENUNGGU_VERIFIKASI" {{ request('status') == 'MENUNGGU_VERIFIKASI' ? 'selected' : '' }}>Menunggu verifikasi</option>
                <option value="DITERIMA" {{ request('status') == 'DITERIMA' ? 'selected' : '' }}>Diterima (sesuai)</option>
                <option value="DITOLAK" {{ request('status') == 'DITOLAK' ? 'selected' : '' }}>Ditolak (tidak sesuai)</option>
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
                    placeholder="Cari no penerimaan atau SBBK..."
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
        <table
            class="w-full min-w-[920px] table-fixed divide-y divide-gray-200"
            @if($penerimaans instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $penerimaans->firstItem() }}" @endif
        >
            <colgroup>
                <col style="width:3rem">
                <col style="width:12%">
                <col style="width:11%">
                <col style="width:20%">
                <col style="width:17%">
                <col style="width:9%">
                <col style="width:13%">
                <col style="width:15%">
            </colgroup>
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th class="!px-3" />
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Penerimaan</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No SBBK</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai Penerima</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($penerimaans as $penerimaan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td class="!px-3" :paginator="$penerimaans" />
                        <td class="px-3 py-3 align-top">
                            <div class="text-sm font-medium text-gray-900 truncate" title="{{ $penerimaan->no_penerimaan }}">{{ $penerimaan->no_penerimaan }}</div>
                        </td>
                        <td class="px-3 py-3 align-top">
                            <div class="text-sm text-gray-900 truncate" title="{{ $penerimaan->distribusi->no_sbbk ?? '-' }}">
                                <a href="{{ route('transaction.distribusi.show', $penerimaan->distribusi->id_distribusi) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $penerimaan->distribusi->no_sbbk ?? '-' }}
                                </a>
                            </div>
                        </td>
                        <td class="px-3 py-3 align-top">
                            <div class="text-sm text-gray-900 truncate" title="{{ $penerimaan->unitKerja->nama_unit_kerja ?? '-' }}">{{ $penerimaan->unitKerja->nama_unit_kerja ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 align-top">
                            <div class="text-sm text-gray-900 truncate" title="{{ $penerimaan->pegawaiPenerima->nama_pegawai ?? '-' }}">{{ $penerimaan->pegawaiPenerima->nama_pegawai ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 tabular-nums align-top">
                            {{ $penerimaan->tanggal_penerimaan->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-3 align-top">
                            @php
                                $statusColor = match($penerimaan->status_penerimaan) {
                                    'MENUNGGU_VERIFIKASI' => 'bg-amber-100 text-amber-900',
                                    'DITERIMA' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusLabel = match($penerimaan->status_penerimaan) {
                                    'MENUNGGU_VERIFIKASI' => 'Menunggu verifikasi',
                                    default => $penerimaan->status_penerimaan,
                                };
                            @endphp
                            <span class="inline-block max-w-full truncate px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}" title="{{ $statusLabel }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right text-sm font-medium align-top">
                            @php
                                $user = auth()->user();
                            @endphp
                            <div class="flex items-center justify-end space-x-2 flex-wrap gap-1">
                                @if($penerimaan->status_penerimaan === 'MENUNGGU_VERIFIKASI' && (\App\Helpers\PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.update') || \App\Helpers\PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.store')))
                                <a
                                    href="{{ route('transaction.penerimaan-barang.show', $penerimaan->id_penerimaan) }}#verifikasi"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-900 bg-amber-100 rounded-md hover:bg-amber-200 transition-colors"
                                    title="Verifikasi barang"
                                >
                                    Verifikasi
                                </a>
                                @endif
                                <a 
                                    href="{{ route('transaction.penerimaan-barang.show', $penerimaan->id_penerimaan) }}" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                                    title="Detail"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                </a>
                                @if($penerimaan->status_penerimaan === 'DITOLAK' && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.edit'))
                                <a 
                                    href="{{ route('transaction.penerimaan-barang.edit', $penerimaan->id_penerimaan) }}" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-md hover:bg-indigo-200 transition-colors"
                                    title="Koreksi (penerimaan tidak sesuai)"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endif
                                @if($penerimaan->status_penerimaan !== 'DITERIMA' && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.destroy'))
                                <form 
                                    action="{{ route('transaction.penerimaan-barang.destroy', $penerimaan->id_penerimaan) }}" 
                                    method="POST" 
                                    class="inline" 
                                    data-confirm="Apakah Anda yakin ingin menghapus penerimaan ini?"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200 transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Belum ada data. Penerimaan dibuat otomatis setelah distribusi dikirim.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($penerimaans->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $penerimaans->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('#unit_kerja, #status').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
@endsection

