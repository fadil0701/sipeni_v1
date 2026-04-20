@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Status Perencanaan (RKU)</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar Rencana Kebutuhan Unit (RKU) dan status pengajuan</p>
    </div>
    <div class="flex items-center gap-2">
        <a 
            href="{{ route('planning.rekap-tahunan') }}" 
            class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Rekap Tahunan
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
@endif
@if(session('info'))
    <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
    </div>
@endif

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('planning.rku.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label for="status_rku" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
                id="status_rku" 
                name="status_rku" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Status</option>
                <option value="DRAFT" {{ request('status_rku') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="DIAJUKAN" {{ request('status_rku') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                <option value="DISETUJUI" {{ request('status_rku') == 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                <option value="DITOLAK" {{ request('status_rku') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                <option value="DIPROSES" {{ request('status_rku') == 'DIPROSES' ? 'selected' : '' }}>Diproses</option>
            </select>
        </div>
        <div>
            <label for="tahun_anggaran" class="block text-sm font-medium text-gray-700 mb-1">Tahun Anggaran</label>
            <select 
                id="tahun_anggaran" 
                name="tahun_anggaran" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Tahun</option>
                @foreach($tahunList ?? [] as $t)
                    <option value="{{ $t }}" {{ request('tahun_anggaran') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
            <select 
                id="id_unit_kerja" 
                name="id_unit_kerja" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Unit Kerja</option>
                @foreach($unitKerjaList ?? [] as $uk)
                    <option value="{{ $uk->id_unit_kerja }}" {{ request('id_unit_kerja') == $uk->id_unit_kerja ? 'selected' : '' }}>{{ $uk->nama_unit_kerja }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
            <button 
                type="submit" 
                class="flex-1 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filter
            </button>
            <a 
                href="{{ route('planning.rku.index') }}" 
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. RKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program / Kegiatan / Sub Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Anggaran</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rkus as $rku)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $rku->no_rku }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $rku->unitKerja?->nama_unit_kerja ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($rku->subKegiatan)
                            <div>{{ $rku->subKegiatan->kegiatan?->program?->nama_program ?? '-' }}</div>
                            <div class="text-gray-600">{{ $rku->subKegiatan->kegiatan?->nama_kegiatan ?? '-' }}</div>
                            <div class="text-gray-500">{{ $rku->subKegiatan->nama_sub_kegiatan }} ({{ $rku->subKegiatan->kode_sub_kegiatan ?? '-' }})</div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $rku->tahun_anggaran }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $rku->tanggal_pengajuan ? $rku->tanggal_pengajuan->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                        Rp {{ number_format($rku->total_anggaran ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $statusColors = [
                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                                'DISETUJUI' => 'bg-green-100 text-green-800',
                                'DITOLAK' => 'bg-red-100 text-red-800',
                                'DIPROSES' => 'bg-blue-100 text-blue-800',
                            ];
                            $color = $statusColors[$rku->status_rku] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">{{ $rku->status_rku }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('planning.rku.show', $rku->id_rku) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data RKU</h3>
                        <p class="mt-1 text-sm text-gray-500">Belum ada Rencana Kebutuhan Unit (RKU) yang terdaftar.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rkus->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $rkus->links() }}
        </div>
    @endif
</div>
@endsection
