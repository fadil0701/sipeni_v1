@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.permintaan-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Permintaan Barang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Permintaan Barang</h2>
            <p class="text-sm text-gray-600 mt-1">No. Permintaan: <span class="font-semibold">{{ $permintaan->no_permintaan }}</span></p>
        </div>
        <div class="flex space-x-3">
            @if($permintaan->status->value === 'draft')
                <a 
                    href="{{ route('transaction.permintaan-barang.edit', $permintaan->id_permintaan) }}" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <form 
                    action="{{ route('transaction.permintaan-barang.ajukan', $permintaan->id_permintaan) }}" 
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
                        <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->no_permintaan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $permintaan->status->badgeClasses() }}">
                                {{ $permintaan->status->label() }}
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
                        <dt class="text-sm font-medium text-gray-500 mb-1">Jenis Permintaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $jenisPermintaan = is_array($permintaan->jenis_permintaan) ? $permintaan->jenis_permintaan : (is_string($permintaan->jenis_permintaan) ? json_decode($permintaan->jenis_permintaan, true) : []);
                            @endphp
                            @if(is_array($jenisPermintaan) && count($jenisPermintaan) > 0)
                                @foreach($jenisPermintaan as $jenis)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $jenis == 'ASET' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }} mr-1">
                                        {{ $jenis }}
                                    </span>
                                @endforeach
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    {{ is_string($permintaan->jenis_permintaan) ? $permintaan->jenis_permintaan : '-' }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if($permintaan->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $permintaan->keterangan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Detail Permintaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Permintaan ({{ $permintaan->detailPermintaan->count() }} item)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Diminta</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($permintaan->detailPermintaan as $index => $detail)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $detail->dataBarang?->kode_data_barang ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->dataBarang?->nama_barang ?? $detail->deskripsi_barang ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($detail->qty_diminta, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Alur Approval PPKP -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alur Approval PPKP</h3>
                @if(isset($approvalFlow) && $approvalFlow->count() > 0)
                    <div class="space-y-3">
                        @foreach($approvalFlow as $flow)
                            @php
                                $log = (isset($approvalHistory) ? $approvalHistory->firstWhere('id_approval_flow', $flow->id) : null);
                                $stepStatus = $log?->status ?? 'BELUM_DIAJUKAN';
                                $stepBadge = match($stepStatus) {
                                    'DISETUJUI', 'DIKETAHUI', 'DIVERIFIKASI', 'DIDISPOSISIKAN', 'DIPROSES' => 'bg-green-100 text-green-800',
                                    'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                $stepLabel = $stepStatus === 'BELUM_DIAJUKAN' ? 'Belum diajukan' : $stepStatus;
                            @endphp
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            Step {{ $flow->step_order }} - {{ $flow->nama_step }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-600">
                                            Role: {{ $flow->role->display_name ?? $flow->role->name ?? 'N/A' }}
                                        </p>
                                        @if($log?->user)
                                            <p class="mt-1 text-xs text-gray-600">
                                                Diproses oleh: {{ $log->user->name }}
                                            </p>
                                        @endif
                                        @if($log?->catatan)
                                            <p class="mt-2 text-sm text-gray-700">{{ $log->catatan }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $stepBadge }}">
                                            {{ $stepLabel }}
                                        </span>
                                        @if($log)
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ ($log->approved_at ?? $log->created_at)?->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                        Alur approval belum dikonfigurasi.
                    </div>
                @endif
            </div>

            <!-- Riwayat Persetujuan -->
            @if(isset($approvalHistory) && $approvalHistory->count() > 0)
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Persetujuan</h3>
                    <div class="space-y-3">
                        @foreach($approvalHistory as $log)
                            @php
                                $statusClasses = match($log->status) {
                                    'DISETUJUI', 'DIKETAHUI', 'DIVERIFIKASI', 'DIDISPOSISIKAN', 'DIPROSES' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex justify-between items-start gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $log->approvalFlow?->nama_step ?? 'Step approval' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $log->user?->name ?? ($log->role?->display_name ?? 'System') }}
                                        </p>
                                        @if($log->catatan)
                                            <p class="text-sm text-gray-700 mt-2">{{ $log->catatan }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses }}">
                                            {{ $log->status }}
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ ($log->approved_at ?? $log->created_at)?->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

