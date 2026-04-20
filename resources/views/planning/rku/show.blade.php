@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail RKU</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $rku->no_rku ?? '-' }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a 
            href="{{ route('planning.rku.index') }}" 
            class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>
</div>

@if(session('info'))
    <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Info Header -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi RKU</h3>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">No. RKU</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->no_rku }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Tahun Anggaran</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->tahun_anggaran }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Tanggal Pengajuan</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->tanggal_pengajuan ? $rku->tanggal_pengajuan->format('d/m/Y') : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Jenis</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->jenis_rku ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
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
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Unit Kerja</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->unitKerja?->nama_unit_kerja ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Program / Kegiatan / Sub Kegiatan</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($rku->subKegiatan)
                        {{ $rku->subKegiatan->kegiatan?->program?->nama_program ?? '-' }} &rarr;
                        {{ $rku->subKegiatan->kegiatan?->nama_kegiatan ?? '-' }} &rarr;
                        {{ $rku->subKegiatan->nama_sub_kegiatan }} ({{ $rku->subKegiatan->kode_sub_kegiatan ?? '-' }})
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Pengaju</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->pengaju?->nama ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total Anggaran</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">Rp {{ number_format($rku->total_anggaran ?? 0, 0, ',', '.') }}</dd>
            </div>
            @if($rku->keterangan)
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->keterangan }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

<!-- Detail Barang -->
<div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <h3 class="px-6 py-4 border-b border-gray-200 bg-gray-50 text-lg font-semibold text-gray-900">Detail Barang / Rencana Kebutuhan</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Rencana</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rku->rkuDetail ?? [] as $index => $detail)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->dataBarang?->nama_barang ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($detail->qty_rencana ?? 0, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $detail->satuan?->nama_satuan ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">Rp {{ number_format($detail->harga_satuan_rencana ?? 0, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">Rp {{ number_format($detail->subtotal_rencana ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada detail barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
