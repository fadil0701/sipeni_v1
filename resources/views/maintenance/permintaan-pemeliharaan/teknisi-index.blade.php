@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Daftar Permintaan Pemeliharaan</h1>
        <p class="mt-1 text-sm text-gray-600">
            @if(($viewType ?? 'aktif') === 'riwayat')
                Riwayat permintaan yang sudah selesai dikerjakan atau ditutup
            @else
                Antrian pengerjaan teknisi — permintaan yang sudah didisposisi Pengurus Barang
            @endif
        </p>
    </div>
</div>

<div class="mb-6 bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex" aria-label="Tabs">
            @php $currentViewType = $viewType ?? 'aktif'; @endphp
            <a
                href="{{ route('maintenance.daftar-permintaan-pemeliharaan.index', ['view_type' => 'aktif']) }}"
                class="px-6 py-3 text-sm font-medium {{ $currentViewType === 'aktif' ? 'border-b-2 border-blue-500 text-blue-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Perlu Dikerjakan
            </a>
            <a
                href="{{ route('maintenance.daftar-permintaan-pemeliharaan.index', ['view_type' => 'riwayat']) }}"
                class="px-6 py-3 text-sm font-medium {{ $currentViewType === 'riwayat' ? 'border-b-2 border-blue-500 text-blue-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Riwayat
            </a>
        </nav>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('maintenance.daftar-permintaan-pemeliharaan.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
        <input type="hidden" name="view_type" value="{{ $viewType ?? 'aktif' }}">
        @if($unitKerjas->isNotEmpty())
        <div>
            <label for="unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
            <select id="unit_kerja" name="unit_kerja" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua Unit Kerja</option>
                @foreach($unitKerjas as $unitKerja)
                    <option value="{{ $unitKerja->id_unit_kerja }}" {{ request('unit_kerja') == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                        {{ $unitKerja->nama_unit_kerja }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua Status</option>
                @if(($viewType ?? 'aktif') === 'riwayat')
                    <option value="SELESAI" {{ request('status') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                    <option value="DITOLAK" {{ request('status') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                    <option value="DIBATALKAN" {{ request('status') == 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan</option>
                @else
                    <option value="DIPROSES" {{ request('status') == 'DIPROSES' ? 'selected' : '' }}>Diproses</option>
                    <option value="MENUNGGU_DIKETAHUI_SR" {{ request('status') == 'MENUNGGU_DIKETAHUI_SR' ? 'selected' : '' }}>Menunggu Diketahui SR</option>
                    <option value="MENUNGGU_PENGADAAN" {{ request('status') == 'MENUNGGU_PENGADAAN' ? 'selected' : '' }}>Menunggu Pengadaan</option>
                @endif
            </select>
        </div>

        <div>
            <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
            <select id="jenis" name="jenis" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua Jenis</option>
                <option value="RUTIN" {{ request('jenis') == 'RUTIN' ? 'selected' : '' }}>Rutin</option>
                <option value="KALIBRASI" {{ request('jenis') == 'KALIBRASI' ? 'selected' : '' }}>Kalibrasi</option>
                <option value="PERBAIKAN" {{ request('jenis') == 'PERBAIKAN' ? 'selected' : '' }}>Perbaikan</option>
                <option value="PENGGANTIAN_SPAREPART" {{ request('jenis') == 'PENGGANTIAN_SPAREPART' ? 'selected' : '' }}>Penggantian Sparepart</option>
            </select>
        </div>

        <div>
            <label for="prioritas" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
            <select id="prioritas" name="prioritas" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua Prioritas</option>
                <option value="RENDAH" {{ request('prioritas') == 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                <option value="SEDANG" {{ request('prioritas') == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                <option value="TINGGI" {{ request('prioritas') == 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                <option value="DARURAT" {{ request('prioritas') == 'DARURAT' ? 'selected' : '' }}>Darurat</option>
            </select>
        </div>

        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input
                type="text"
                id="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="No permintaan / register / pemohon..."
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>
    </form>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($permintaans instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $permintaans->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Permintaan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Register</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelaksana</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($permintaans as $permintaan)
                    @php
                        $hasServiceReport = (bool) $permintaan->serviceReport;
                        $canProsesPengerjaan = $permintaan->canStartServiceReport()
                            && \App\Helpers\PermissionHelper::canAccess(auth()->user(), 'maintenance.service-report.create');
                        $canLanjutPerbaikan = $permintaan->canResumeRepairAfterPurchase()
                            && \App\Helpers\PermissionHelper::canAccess(auth()->user(), 'maintenance.permintaan-pemeliharaan.lanjut-perbaikan');
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <x-table.num-td :paginator="$permintaans" />
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $permintaan->no_permintaan_pemeliharaan }}</div>
                            <div class="text-xs text-gray-500">{{ $permintaan->tanggal_permintaan->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $permintaan->registerAset->nomor_register ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $permintaan->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $permintaan->jenis_pelaksana ?? '-' }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $permintaan->pegawaiPelaksana->nama_pegawai ?? ($permintaan->nama_vendor ?? '') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ \App\Support\UiColor::badgeForStatus($permintaan->jenis_pemeliharaan) }}">
                                {{ $permintaan->jenis_pemeliharaan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ \App\Support\UiColor::badgeForStatus($permintaan->prioritas) }}">
                                {{ $permintaan->prioritas }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ \App\Support\UiColor::badgeForStatus($permintaan->status_permintaan) }}">
                                {{ $permintaan->status_permintaan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <x-ui.btn
                                    action="detail"
                                    size="sm"
                                    soft
                                    href="{{ route('maintenance.permintaan-pemeliharaan.show', $permintaan->id_permintaan_pemeliharaan) }}"
                                >
                                    Detail
                                </x-ui.btn>
                                @if($canLanjutPerbaikan)
                                    <form
                                        action="{{ route('maintenance.permintaan-pemeliharaan.lanjut-perbaikan', $permintaan->id_permintaan_pemeliharaan) }}"
                                        method="POST"
                                        class="inline"
                                        data-confirm="Tandai pembelian selesai dan lanjutkan perbaikan?"
                                    >
                                        @csrf
                                        <x-ui.btn action="lanjut" size="sm" type="submit">
                                            Lanjut Perbaikan
                                        </x-ui.btn>
                                    </form>
                                @endif
                                @if($canProsesPengerjaan)
                                    <x-ui.btn
                                        action="proses"
                                        size="sm"
                                        href="{{ route('maintenance.service-report.create', ['permintaan_id' => $permintaan->id_permintaan_pemeliharaan]) }}"
                                    >
                                        Proses
                                    </x-ui.btn>
                                @elseif($hasServiceReport)
                                    <x-ui.btn
                                        action="detail"
                                        size="sm"
                                        soft
                                        href="{{ route('maintenance.service-report.show', $permintaan->serviceReport->id_service_report) }}"
                                    >
                                        Laporan Servis
                                    </x-ui.btn>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(($viewType ?? 'aktif') === 'riwayat')
                                    Belum ada riwayat pengerjaan.
                                @else
                                    Belum ada permintaan yang siap dikerjakan teknisi.
                                    Pastikan Pengurus Barang sudah melakukan disposisi.
                                @endif
                            </p>
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
@endsection
