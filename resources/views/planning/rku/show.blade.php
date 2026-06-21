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

<!-- Action Buttons -->
@php
$canSubmit = isset($availableTransitions['submit']);
$canCancel = isset($availableTransitions['cancel']);
$canReview = isset($availableTransitions['review']);
$canApprove = isset($availableTransitions['approve']) || isset($availableTransitions['forward']);
$approveLabel = $availableTransitions['forward']['label'] ?? ($availableTransitions['approve']['label'] ?? 'Setujui');
$canReject = isset($availableTransitions['reject']);
$canRevise = isset($availableTransitions['revise']);
$canRequestRevision = isset($availableTransitions['request_revision']);
$canEdit = Gate::allows('update', $rku);
$canDelete = $rku->status_rku === 'DRAFT' && Gate::allows('delete', $rku);
$statusLabel = \App\Models\RkuHeader::STATUSES[$rku->status_rku] ?? $rku->status_rku;
@endphp

@if($canSubmit || $canCancel || $canReview || $canApprove || $canReject || $canRevise || $canRequestRevision || $canEdit || $canDelete)
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <div class="flex flex-wrap items-center gap-2">
        @if($canEdit)
        <a 
            href="{{ route('planning.rku.edit', $rku->id_rku) }}" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </a>
        @endif
        @if($canSubmit)
        <form method="POST" action="{{ route('planning.rku.submit', $rku->id_rku) }}" class="inline" data-confirm="Ajukan RKU untuk approval?">
            @csrf
            <button 
                type="submit" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Ajukan
            </button>
        </form>
        @endif
        @if($canCancel)
        <form method="POST" action="{{ route('planning.rku.cancel', $rku->id_rku) }}" class="inline" data-confirm="Batalkan pengajuan RKU?">
            @csrf
            <input type="hidden" name="notes" value="Dibatalkan oleh user">
            <button 
                type="submit" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Batalkan
            </button>
        </form>
        @endif
        @if($canReview)
        <form method="POST" action="{{ route('planning.rku.startReview', $rku->id_rku) }}" class="inline" data-confirm="Mulai review RKU?">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $availableTransitions['review']['label'] ?? 'Review' }}
            </button>
        </form>
        @endif
        @if($canApprove)
        <button
            type="button"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            onclick="document.getElementById('approveModal').classList.remove('hidden')"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ $approveLabel }}
        </button>
        @endif
        @if($canReject)
        <button 
            type="button" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            onclick="document.getElementById('rejectModal').classList.remove('hidden')"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Tolak
        </button>
        @endif
        @if($canRequestRevision)
        <button
            type="button"
            class="inline-flex items-center px-4 py-2 border border-amber-300 text-sm font-medium rounded-md shadow-sm text-amber-800 bg-amber-50 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
            onclick="document.getElementById('revisionModal').classList.remove('hidden')"
        >
            {{ $availableTransitions['request_revision']['label'] ?? 'Minta Revisi' }}
        </button>
        @endif
        @if($canRevise)
        <form method="POST" action="{{ route('planning.rku.revise', $rku->id_rku) }}" class="inline" data-confirm="Kembalikan ke draft untuk revisi?">
            @csrf
            <button 
                type="submit" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Revisi
            </button>
        </form>
        @endif
        @if($canDelete)
        <form method="POST" action="{{ route('planning.rku.destroy', $rku->id_rku) }}" class="inline" data-confirm="Hapus RKU ini? Tindakan ini tidak dapat dibatalkan.">
            @csrf
            @method('DELETE')
            <button 
                type="submit" 
                class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md shadow-sm text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
            </button>
        </form>
        @endif
    </div>
</div>
@endif

@if($canApprove)
<div id="approveModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="document.getElementById('approveModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form method="POST" action="{{ route('planning.rku.approve', $rku->id_rku) }}">
                @csrf
                <h3 class="text-lg font-medium text-gray-900">{{ $approveLabel }}</h3>
                <p class="mt-1 text-sm text-gray-500">Komentar approval (opsional):</p>
                <textarea name="notes" rows="3" class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Catatan..."></textarea>
                <div class="mt-5 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-sm font-medium text-white hover:bg-green-700">Simpan</button>
                    <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="document.getElementById('approveModal').classList.add('hidden')">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($canRequestRevision)
<div id="revisionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('revisionModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form method="POST" action="{{ route('planning.rku.revise', $rku->id_rku) }}">
                @csrf
                <input type="hidden" name="request_revision" value="1">
                <h3 class="text-lg font-medium text-gray-900">Minta Revisi</h3>
                <textarea name="notes" required rows="3" class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm" placeholder="Catatan revisi..."></textarea>
                <div class="mt-5 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="inline-flex justify-center rounded-md px-4 py-2 bg-amber-600 text-sm font-medium text-white hover:bg-amber-700">Kirim</button>
                    <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-white text-sm text-gray-700" onclick="document.getElementById('revisionModal').classList.add('hidden')">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Reject Modal -->
@if($canReject)
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form method="POST" action="{{ route('planning.rku.reject', $rku->id_rku) }}">
                @csrf
                <div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tolak RKU</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Berikan alasan penolakan:</p>
                            <textarea 
                                name="notes" 
                                required
                                rows="3" 
                                class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                placeholder="Alasan penolakan..."
                            ></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Tolak RKU
                    </button>
                    <button 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                        onclick="document.getElementById('rejectModal').classList.add('hidden')"
                    >
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
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
                <dd class="mt-1 text-sm text-gray-900">{{ $rku->jenis_label ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    @php
                        $statusColors = [
                            'DRAFT' => 'bg-gray-100 text-gray-800',
                            'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                            'REVIEW_KASUBAG_TU' => 'bg-indigo-100 text-indigo-800',
                            'REVIEW_KEPALA_PUSAT' => 'bg-purple-100 text-purple-800',
                            'DISETUJUI' => 'bg-green-100 text-green-800',
                            'DITOLAK' => 'bg-red-100 text-red-800',
                            'REVISION_REQUIRED' => 'bg-amber-100 text-amber-800',
                            'DIPROSES' => 'bg-blue-100 text-blue-800',
                        ];
                        $color = $statusColors[$rku->status_rku] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">{{ $statusLabel }}</span>
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

    @if(isset($workflowHistories) && $workflowHistories->isNotEmpty())
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Workflow</h3>
        <ol class="relative border-l border-gray-200 ml-3 space-y-6">
            @foreach($workflowHistories as $history)
            <li class="ml-6">
                <span class="absolute -left-1.5 flex h-3 w-3 rounded-full bg-blue-600 ring-4 ring-white"></span>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="font-semibold text-gray-900">{{ $history->step?->name ?? $history->action }}</span>
                    <span class="text-gray-400">·</span>
                    <span class="text-gray-600">{{ $history->user?->name ?? 'Sistem' }}</span>
                    <span class="text-gray-400">·</span>
                    <time class="text-gray-500">{{ $history->created_at?->format('d/m/Y H:i') }}</time>
                </div>
                @if($history->comment)
                <p class="mt-1 text-sm text-gray-600">{{ $history->comment }}</p>
                @endif
            </li>
            @endforeach
        </ol>
    </div>
    @endif
</div>

<!-- Detail Barang -->
<div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <h3 class="px-6 py-4 border-b border-gray-200 bg-gray-50 text-lg font-semibold text-gray-900">Detail Barang / Rencana Kebutuhan</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $detail->jenis_rku_label }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_item ?? $detail->dataBarang?->nama_barang ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($detail->qty_rencana ?? 0, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $detail->satuan?->nama_satuan ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">Rp {{ number_format($detail->harga_satuan_rencana ?? 0, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">Rp {{ number_format($detail->subtotal_rencana ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada detail barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
