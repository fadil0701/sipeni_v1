@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.approval.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Persetujuan
    </a>
</div>

{{-- Alert Messages --}}
@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
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
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">Terjadi kesalahan:</p>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if($permintaan)
    @php
        $user = auth()->user();
        $stepOrder = $currentFlow?->step_order ?? 0;
        $canKoreksi = ($stepOrder == 3 && ($user->hasRole('kasubbag_tu') || $user->hasRole('admin')) && $approval->status === 'MENUNGGU');
        // Aturan tampilan kolom:
        // - Step 2 (Kepala Unit): jangan tampilkan "Stock Tersedia"
        // - Step 3 (Kepala Subbag/Kasubbag TU): tampilkan "Stock Tersedia"
        $showStockTersedia = $stepOrder >= 3;
    @endphp
    
    @if($canKoreksi)
    <form method="POST" action="{{ route('transaction.approval.verifikasi', $approval->id) }}" id="formVerifikasi">
        @csrf
    @endif
    
    <!-- Detail Permintaan -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Detail Permintaan Barang</h2>
            <p class="text-sm text-gray-600 mt-1">No. Permintaan: <span class="font-semibold">{{ $permintaan->no_permintaan }}</span></p>
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
                                    @if($showStockTersedia)
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Tersedia</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Diminta</th>
                                    @if($canKoreksi)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Koreksi Qty</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($permintaan->detailPermintaan as $index => $detail)
                                @php
                                    $detailStock = $stockData[$detail->id_detail_permintaan] ?? ['total' => 0, 'per_gudang' => collect()];
                                    $totalStock = $detailStock['total'];
                                    // Hanya tampilkan "melebihi stock" jika barang punya stock dan qty melebihi; barang tanpa stock/stock 0 tetap dapat didisposisikan
                                    $isOverStock = $detail->id_data_barang && $totalStock > 0 && $detail->qty_diminta > $totalStock;
                                @endphp
                                <tr class="{{ $isOverStock ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $detail->dataBarang?->kode_data_barang ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->dataBarang?->nama_barang ?? $detail->deskripsi_barang ?? '-' }}</td>
                                    @if($showStockTersedia)
                                        <td class="px-4 py-3 text-sm">
                                            @if($detail->id_data_barang)
                                            <span class="font-semibold {{ $totalStock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($totalStock, 2, ',', '.') }}
                                            </span>
                                            @if($detailStock['per_gudang']->isNotEmpty())
                                            <div class="text-xs text-gray-500 mt-1">
                                                @foreach($detailStock['per_gudang'] as $stockGudang)
                                                    <div>{{ $stockGudang['nama_gudang'] }}: {{ number_format($stockGudang['qty_akhir'], 2, ',', '.') }}</div>
                                                @endforeach
                                            </div>
                                            @endif
                                            @else
                                            <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-sm">
                                        <span class="{{ $isOverStock ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                            {{ number_format($detail->qty_diminta, 2, ',', '.') }}
                                        </span>
                                        @if($isOverStock)
                                        <div class="text-xs text-red-600 mt-1">⚠ Melebihi stock!</div>
                                        @endif
                                    </td>
                                    @if($canKoreksi)
                                    <td class="px-4 py-3 text-sm">
                                        <input 
                                            type="number" 
                                            name="koreksi_qty[{{ $detail->id_detail_permintaan }}]" 
                                            form="formVerifikasi"
                                            value="{{ old('koreksi_qty.'.$detail->id_detail_permintaan, $detail->qty_diminta) }}"
                                            min="0.01"
                                            step="0.01"
                                            @if($detail->id_data_barang && $totalStock > 0) max="{{ $totalStock }}" @endif
                                            class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="{{ number_format($detail->qty_diminta, 2, ',', '.') }}"
                                        >
                                    </td>
                                    @endif
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Approval History -->
@if(isset($approvalHistory) && $approvalHistory->count() > 0)
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Riwayat Persetujuan</h2>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($approvalHistory as $index => $hist)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        @php
                                            $statusIcon = match($hist->status) {
                                                'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                                                'DIKETAHUI' => 'bg-blue-100 text-blue-800',
                                                'DIVERIFIKASI' => 'bg-purple-100 text-purple-800',
                                                'DISETUJUI' => 'bg-green-100 text-green-800',
                                                'DITOLAK' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $statusIcon }}">
                                            @if($hist->status === 'DISETUJUI')
                                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @elseif($hist->status === 'DITOLAK')
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
                                                <span class="font-medium text-gray-900">{{ $hist->approvalFlow?->nama_step ?? 'Step ' . ($hist->approvalFlow?->step_order ?? '-') }}</span>
                                                @if($hist->user)
                                                    oleh <span class="font-medium">{{ $hist->user->name ?? 'N/A' }}</span>
                                                @endif
                                            </p>
                                            @if($hist->catatan)
                                                <p class="mt-1 text-sm text-gray-600">{{ $hist->catatan }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            @if($hist->approved_at)
                                                {{ $hist->approved_at->format('d/m/Y H:i') }}
                                            @else
                                                {{ $hist->created_at->format('d/m/Y H:i') }}
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

<!-- Form Approval -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Persetujuan</h2>
        <p class="text-sm text-gray-600 mt-1">
            Step: <span class="font-semibold">{{ $currentFlow?->nama_step ?? 'N/A' }}</span> | 
            Status: 
            @php
                $statusToShow = $displayStatus ?? $approval->status;
                $statusColor = match($statusToShow) {
                    'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                    'DIKETAHUI' => 'bg-blue-100 text-blue-800',
                    'DIVERIFIKASI' => 'bg-purple-100 text-purple-800',
                    'DISETUJUI' => 'bg-green-100 text-green-800',
                    'DITOLAK' => 'bg-red-100 text-red-800',
                    'DIDISPOSISIKAN' => 'bg-indigo-100 text-indigo-800',
                    'DIPROSES' => 'bg-blue-100 text-blue-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                {{ $statusToShow }}
            </span>
            @if($rejectedApproval && $rejectedApproval->id != $approval->id)
                <span class="ml-2 text-xs text-red-600">
                    (Ditolak di step: {{ $rejectedApproval->approvalFlow?->nama_step ?? 'Step ' . ($rejectedApproval->approvalFlow?->step_order ?? '-') }})
                </span>
            @endif
        </p>
    </div>
    
    <div class="p-6">
        @if($approval->status === 'MENUNGGU')
            @php
                $user = auth()->user();
                $stepOrder = $currentFlow?->step_order ?? 0;
                // $previousStepVerified sudah dihitung di controller dan dikirim ke view
            @endphp
            
            {{-- Kepala Unit - Mengetahui (Step 2) --}}
            @if($stepOrder == 2 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.mengetahui'))
                <form method="POST" action="{{ route('transaction.approval.mengetahui', $approval->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="catatan_mengetahui" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea 
                            id="catatan_mengetahui" 
                            name="catatan" 
                            rows="3"
                            placeholder="Masukkan catatan..."
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        ></textarea>
                    </div>
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mengetahui
                    </button>
                </form>
            
            {{-- Kasubbag TU - Verifikasi/Kembalikan (Step 3) --}}
            @elseif($stepOrder == 3 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.verifikasi'))
                <div class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Koreksi Jumlah:</strong> Anda dapat mengoreksi jumlah permintaan jika diperlukan. Untuk barang dari master, pastikan jumlah yang dikoreksi tidak melebihi stock tersedia. Barang tanpa stock (permintaan lainnya) atau stock 0 tetap dapat didisposisikan.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="catatan_verifikasi" class="block text-sm font-medium text-gray-700 mb-2">Catatan Verifikasi (Opsional)</label>
                        <textarea 
                            id="catatan_verifikasi" 
                            name="catatan" 
                            rows="3"
                            form="formVerifikasi"
                            placeholder="Masukkan catatan verifikasi..."
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        ></textarea>
                    </div>
                    <button 
                        type="submit" 
                        form="formVerifikasi"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verifikasi, Setujui & Disposisi
                    </button>

                    <form method="POST" action="{{ route('transaction.approval.kembalikan', $approval->id) }}" id="formKembalikan" data-confirm="Apakah Anda yakin ingin mengembalikan permintaan ini?">
                        @csrf
                        <div class="mb-4">
                            <label for="catatan_kembalikan" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Pengembalian <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="catatan_kembalikan" 
                                name="catatan" 
                                rows="3"
                                form="formKembalikan"
                                placeholder="Masukkan alasan pengembalian (minimal 10 karakter)..."
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm @error('catatan') border-red-500 @enderror"
                            >{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button 
                            type="submit" 
                            form="formKembalikan"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="return validateKembalikan();"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Kembalikan
                        </button>
                    </form>
                </div>
            
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="text-sm text-yellow-700">
                        Anda tidak memiliki hak untuk memproses approval pada step ini.
                    </p>
                </div>
            @endif
        @else
            <!-- Tampilkan informasi jika sudah diproses -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if($approval->status === 'DISETUJUI')
                            <svg class="h-6 w-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        @elseif($approval->status === 'DITOLAK')
                            <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-gray-900">
                            Permintaan {{ $approval->status === 'DISETUJUI' ? 'Disetujui' : ($approval->status === 'DITOLAK' ? 'Ditolak' : $approval->status) }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            @if($approval->user)
                                Oleh: {{ $approval->user->name ?? 'N/A' }}
                            @endif
                        </p>
                        @if($approval->approved_at)
                            <p class="mt-1 text-xs text-gray-500">
                                Tanggal: {{ $approval->approved_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                        @if($approval->catatan)
                            <p class="mt-2 text-sm text-gray-700">
                                <strong>Catatan:</strong> {{ $approval->catatan }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Informasi Disposisi - Setelah verifikasi Kasubbag TU, disposisi dilakukan otomatis --}}
        @php
            // Cek apakah sudah didisposisikan
            // Disposisi ditandai dengan adanya approval log untuk step 4 (disposisi) dengan role admin gudang kategori
            $step4FlowIds = \App\Models\ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('step_order', 4)
                ->whereIn('role_id', function($query) {
                    $query->select('id')
                        ->from('roles')
                        ->whereIn('name', ['admin_gudang_aset', 'admin_gudang_persediaan', 'admin_gudang_farmasi', 'admin_gudang']);
                })
                ->pluck('id');
            
            $sudahDidisposisikan = \App\Models\ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('id_referensi', $approval->id_referensi)
                ->whereIn('id_approval_flow', $step4FlowIds)
                ->exists();
        @endphp
        
        @if($step3Verified && $sudahDidisposisikan)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                Permintaan telah diverifikasi, disetujui, dan didisposisikan ke Admin Gudang/Pengurus Barang. Admin Gudang dapat memproses permintaan ini di menu "Proses Disposisi".
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function validateKembalikan() {
        const catatan = document.getElementById('catatan_kembalikan').value.trim();
        
        if (!catatan) {
            alert('Catatan pengembalian wajib diisi.');
            document.getElementById('catatan_kembalikan').focus();
            return false;
        }
        
        if (catatan.length < 10) {
            alert('Catatan pengembalian minimal 10 karakter.');
            document.getElementById('catatan_kembalikan').focus();
            return false;
        }
        
        return true;
    }
</script>
@endpush
@endsection

