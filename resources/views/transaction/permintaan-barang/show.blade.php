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
                    data-confirm="Apakah Anda yakin ingin mengajukan permintaan ini?"
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
                            @if($permintaan->pemohon?->nama_jabatan)
                                <span class="text-gray-500">({{ $permintaan->pemohon->nama_jabatan }})</span>
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
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $jenis == 'ASET' ? 'bg-blue-100 text-blue-900' : 'bg-green-100 text-green-900' }} mr-1">
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

            <!-- Progress Approval PPKP (hanya langkah yang sudah/sedang diproses) -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Progress Approval</h3>
                @php
                    $progressLogs = isset($approvalHistory)
                        ? $approvalHistory
                            ->filter(fn ($log) => ! in_array($log->status, ['DIBATALKAN'], true))
                            ->sortBy(fn ($log) => [
                                $log->approvalFlow?->step_order ?? 999,
                                $log->created_at?->timestamp ?? 0,
                            ])
                            ->values()
                        : collect();
                    // Prioritas tampilan: langkah MENUNGGU saat ini, lalu langkah terakhir.
                    $currentLog = $progressLogs->firstWhere('status', 'MENUNGGU')
                        ?? $progressLogs->last();
                @endphp
                @if($progressLogs->count() > 0)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    @if($currentLog?->approvalFlow)
                                        Step {{ $currentLog->approvalFlow->step_order }} - {{ $currentLog->approvalFlow->nama_step }}
                                    @else
                                        Step approval
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-gray-600">
                                    Role: {{ $currentLog->approvalFlow?->role?->display_name
                                        ?? $currentLog->approvalFlow?->role?->name
                                        ?? $currentLog->role?->display_name
                                        ?? 'N/A' }}
                                </p>
                                @if($currentLog?->user)
                                    <p class="mt-1 text-xs text-gray-600">
                                        Diproses oleh: {{ $currentLog->user->name }}
                                    </p>
                                @endif
                                @if($currentLog?->catatan)
                                    <p class="mt-2 text-sm text-gray-700">{{ $currentLog->catatan }}</p>
                                @endif
                                @if($progressLogs->count() > 1)
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ $progressLogs->count() }} langkah sudah dilalui
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                @php
                                    $currentStatus = $currentLog?->status ?? 'BELUM_DIAJUKAN';
                                    $currentBadge = \App\Support\UiColor::badgeForStatus($currentStatus);
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $currentBadge }}">
                                    {{ $currentStatus }}
                                </span>
                                @if($currentLog)
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ ($currentLog->approved_at ?? $currentLog->created_at)?->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($progressLogs->count() > 1)
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sm font-medium text-blue-700 hover:text-blue-800">
                                Lihat riwayat langkah
                            </summary>
                            <div class="mt-3 space-y-3">
                                @foreach($progressLogs as $log)
                                    @php
                                        $statusClasses = \App\Support\UiColor::badgeForStatus($log->status);
                                    @endphp
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="flex justify-between items-start gap-3">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if($log->approvalFlow)
                                                        Step {{ $log->approvalFlow->step_order }} - {{ $log->approvalFlow->nama_step }}
                                                    @else
                                                        Step approval
                                                    @endif
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
                        </details>
                    @endif
                @else
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                        Belum ada progress approval. Permintaan belum diajukan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

