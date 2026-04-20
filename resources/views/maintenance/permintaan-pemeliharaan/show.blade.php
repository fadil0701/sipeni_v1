@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('maintenance.permintaan-pemeliharaan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Permintaan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Permintaan Pemeliharaan</h2>
            <p class="text-sm text-gray-600 mt-1">No. Permintaan: <span class="font-semibold">{{ $permintaan->no_permintaan_pemeliharaan }}</span></p>
        </div>
        <div class="flex space-x-3">
            @if($permintaan->status_permintaan == 'DRAFT')
                <a 
                    href="{{ route('maintenance.permintaan-pemeliharaan.edit', $permintaan->id_permintaan_pemeliharaan) }}" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <form 
                    action="{{ route('maintenance.permintaan-pemeliharaan.ajukan', $permintaan->id_permintaan_pemeliharaan) }}" 
                    method="POST" 
                    class="inline"
                    onsubmit="return confirm('Apakah Anda yakin ingin mengajukan permintaan ini?');"
                >
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ajukan
                    </button>
                </form>
            @endif
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Permintaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Permintaan</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Permintaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->no_permintaan_pemeliharaan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
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
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pemohon</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            {{ $permintaan->pemohon->nama_pegawai ?? '-' }}
                            @if($permintaan->pemohon && $permintaan->pemohon->jabatan)
                                <span class="text-gray-500">({{ $permintaan->pemohon->jabatan->nama_jabatan }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Permintaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->tanggal_permintaan->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Jenis Pemeliharaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">
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
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Prioritas</dt>
                        <dd class="text-sm font-semibold text-gray-900">
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
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informasi Aset -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Aset</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nomor Register</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->registerAset->nomor_register ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama Barang</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Kode Barang</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->registerAset->inventory->dataBarang->kode_data_barang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status Aset</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $permintaan->registerAset->status_aset ?? '-' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Deskripsi Kerusakan -->
            @if($permintaan->deskripsi_kerusakan)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Deskripsi Kerusakan / Masalah</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $permintaan->deskripsi_kerusakan }}</p>
                </div>
            </div>
            @endif

            <!-- Keterangan -->
            @if($permintaan->keterangan)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Keterangan</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $permintaan->keterangan }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approval History -->
@if(isset($approvalLogs) && $approvalLogs->count() > 0)
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Riwayat Persetujuan</h2>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($approvalLogs as $index => $log)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        @php
                                            $statusIcon = match($log->status) {
                                                'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                                                'DIKETAHUI' => 'bg-blue-100 text-blue-800',
                                                'DIVERIFIKASI' => 'bg-purple-100 text-purple-800',
                                                'DISETUJUI' => 'bg-green-100 text-green-800',
                                                'DITOLAK' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $statusIcon }}">
                                            @if($log->status === 'DISETUJUI')
                                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @elseif($log->status === 'DITOLAK')
                                                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900">{{ $log->approvalFlow->nama_step ?? 'Step ' . $log->approvalFlow->step_order }}</span>
                                                @if($log->user)
                                                    oleh <span class="font-medium">{{ $log->user->name ?? 'N/A' }}</span>
                                                @endif
                                            </p>
                                            @if($log->catatan)
                                                <p class="mt-1 text-sm text-gray-600">{{ $log->catatan }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            @if($log->approved_at)
                                                {{ $log->approved_at->format('d/m/Y H:i') }}
                                            @else
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Service Report / Kalibrasi Info -->
@if($permintaan->serviceReport || $permintaan->kalibrasi)
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Hasil Pemeliharaan</h2>
        </div>
        <div class="p-6">
            @if($permintaan->serviceReport)
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Service Report</h3>
                    <p class="text-sm text-gray-600">Service Report sudah dibuat untuk permintaan ini.</p>
                    <a href="{{ route('maintenance.service-report.show', $permintaan->serviceReport->id_service_report) }}" class="mt-2 inline-flex items-center text-blue-600 hover:text-blue-900">
                        Lihat Detail Service Report
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif

            @if($permintaan->kalibrasi)
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Kalibrasi</h3>
                    <p class="text-sm text-gray-600">Data kalibrasi sudah dibuat untuk permintaan ini.</p>
                    <a href="{{ route('maintenance.kalibrasi-aset.show', $permintaan->kalibrasi->id_kalibrasi) }}" class="mt-2 inline-flex items-center text-blue-600 hover:text-blue-900">
                        Lihat Detail Kalibrasi
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection


