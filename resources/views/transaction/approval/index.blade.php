@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Persetujuan Permintaan Barang</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar permintaan barang yang menunggu persetujuan Anda</p>
    </div>
    <a 
        href="{{ route('transaction.approval.diagram') }}" 
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        target="_blank"
    >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Lihat Diagram Flow
    </a>
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('transaction.approval.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
                id="status" 
                name="status" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Status</option>
                <option value="MENUNGGU" {{ request('status') == 'MENUNGGU' ? 'selected' : '' }}>Menunggu</option>
                <option value="DIKETAHUI" {{ request('status') == 'DIKETAHUI' ? 'selected' : '' }}>Diketahui</option>
                <option value="DIVERIFIKASI" {{ request('status') == 'DIVERIFIKASI' ? 'selected' : '' }}>Diverifikasi</option>
                <option value="DISETUJUI" {{ request('status') == 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                <option value="DIDISPOSISIKAN" {{ request('status') == 'DIDISPOSISIKAN' ? 'selected' : '' }}>Didisposisikan</option>
                <option value="DITOLAK" {{ request('status') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
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

        <div class="flex items-end">
            <button 
                type="submit" 
                class="w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filter
            </button>
        </div>

        <div class="flex items-end">
            @if(request('status') || request('tanggal_mulai') || request('tanggal_akhir'))
                <a 
                    href="{{ route('transaction.approval.index') }}" 
                    class="w-full px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 text-center"
                >
                    Reset
                </a>
            @endif
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Permintaan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Step Approval</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($paginator as $item)
                    @php
                        $permintaan = $item['permintaan'];
                        $currentStep = $item['current_step'];
                        $currentStatus = $item['current_status'];
                        $lastCompletedStep = $item['last_completed_step'];
                        
                        // Tentukan warna baris berdasarkan progress approval
                        // Step 2: DIKETAHUI (Kepala Unit) - Biru muda
                        // Step 3: DIVERIFIKASI (Kasubbag TU) - Ungu muda
                        // Step 4: DISETUJUI (Kepala Pusat) - Hijau muda
                        // Step 5: DIDISPOSISIKAN/DIPROSES (Admin Gudang) - Biru tua/Indigo
                        // DITOLAK - Merah muda
                        
                        $rowBgColor = 'bg-white';
                        $rowBorderColor = '';
                        
                        if ($currentStatus === 'DITOLAK') {
                            $rowBgColor = 'bg-red-50';
                            $rowBorderColor = 'border-l-4 border-red-500';
                        } elseif ($lastCompletedStep === null || $lastCompletedStep < 2) {
                            // Belum ada approval atau masih di step awal
                            $rowBgColor = 'bg-gray-50';
                            $rowBorderColor = 'border-l-4 border-gray-400';
                        } elseif ($lastCompletedStep == 2) {
                            // Sudah diketahui oleh Kepala Unit
                            $rowBgColor = 'bg-blue-50';
                            $rowBorderColor = 'border-l-4 border-blue-400';
                        } elseif ($lastCompletedStep == 3 || $currentStatus === 'DISETUJUI') {
                            // Sudah diverifikasi dan disetujui oleh Kasubbag TU (step 3) atau sudah disetujui
                            $rowBgColor = 'bg-green-50';
                            $rowBorderColor = 'border-l-4 border-green-500';
                        } elseif ($lastCompletedStep == 4 || $currentStatus === 'DIDISPOSISIKAN') {
                            // Sudah didisposisikan ke Admin Gudang (step 4)
                            $rowBgColor = 'bg-indigo-50';
                            $rowBorderColor = 'border-l-4 border-indigo-500';
                        } elseif ($lastCompletedStep >= 5 || $currentStatus === 'DIPROSES') {
                            // Sudah diproses oleh Admin Gudang (step 5 atau lebih)
                            $rowBgColor = 'bg-blue-50';
                            $rowBorderColor = 'border-l-4 border-blue-500';
                        }
                        
                        // Status badge color
                        $statusColor = match($currentStatus) {
                            'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                            'DIKETAHUI' => 'bg-blue-100 text-blue-800',
                            'DIVERIFIKASI' => 'bg-purple-100 text-purple-800',
                            'DISETUJUI' => 'bg-green-100 text-green-800',
                            'DIDISPOSISIKAN' => 'bg-indigo-100 text-indigo-800',
                            'DIPROSES' => 'bg-blue-100 text-blue-800',
                            'DITOLAK' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        
                        // Tentukan step yang sedang berjalan
                        $stepName = '-';
                        if ($currentStep && $currentStep->approvalFlow) {
                            $stepName = $currentStep->approvalFlow->nama_step ?? '-';
                        }
                    @endphp
                    <tr class="hover:bg-opacity-80 transition-colors {{ $rowBgColor }} {{ $rowBorderColor }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $permintaan->no_permintaan }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($permintaan->unitKerja && $permintaan->unitKerja->gudang)
                                    @php
                                        $gudangUnits = $permintaan->unitKerja->gudang->where('jenis_gudang', 'UNIT');
                                    @endphp
                                    @if($gudangUnits->count() > 0)
                                        @foreach($gudangUnits as $gudang)
                                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 mr-1">
                                                {{ $gudang->nama_gudang }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $permintaan->pemohon->nama_pegawai ?? '-' }}
                                @if($permintaan->pemohon && $permintaan->pemohon->jabatan)
                                    <div class="text-xs text-gray-500">
                                        ({{ $permintaan->pemohon->jabatan->nama_jabatan ?? '' }})
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $stepName }}
                            </div>
                            @if($currentStep && $currentStep->approvalFlow && $currentStep->approvalFlow->role)
                                <div class="text-xs text-gray-500">
                                    {{ $currentStep->approvalFlow->role->name ?? '' }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $currentStatus }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $permintaan->tanggal_permintaan->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            @if($currentStep)
                                @php
                                    $user = auth()->user();
                                    $stepOrder = $currentStep->approvalFlow->step_order ?? 0;
                                    $canMengetahui = $currentStatus === 'MENUNGGU' && $stepOrder == 2 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.mengetahui');
                                    $canVerifikasi = $currentStatus === 'MENUNGGU' && $stepOrder == 3 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.verifikasi');
                                    $canApprove = $currentStatus === 'MENUNGGU' && $stepOrder == 4 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.approve');
                                    $canReject = $currentStatus === 'MENUNGGU' && $stepOrder == 4 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.reject');
                                @endphp
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    {{-- Detail --}}
                                    <a 
                                        href="{{ route('transaction.approval.show', $currentStep->id) }}" 
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                                        title="Detail"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </a>
                                    {{-- Kepala Unit: Mengetahui (Step 2) --}}
                                    @if($canMengetahui)
                                        <form method="POST" action="{{ route('transaction.approval.mengetahui', $currentStep->id) }}" class="inline" onsubmit="return confirm('Setujui permintaan sebagai Diketahui?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors" title="Mengetahui">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Mengetahui
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Kasubbag TU: Verifikasi (Step 3) - perlu form koreksi di halaman detail --}}
                                    @if($canVerifikasi)
                                        <a 
                                            href="{{ route('transaction.approval.show', $currentStep->id) }}" 
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200 transition-colors"
                                            title="Verifikasi"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                            Verifikasi
                                        </a>
                                    @endif
                                    {{-- Kepala Pusat: Setujui / Tolak (Step 4) --}}
                                    @if($canApprove)
                                        <form method="POST" action="{{ route('transaction.approval.approve', $currentStep->id) }}" class="inline" onsubmit="return confirm('Setujui permintaan ini?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors" title="Setujui">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Setujui
                                            </button>
                                        </form>
                                    @endif
                                    @if($canReject)
                                        <details class="inline relative group/details">
                                            <summary class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors cursor-pointer list-none" title="Tolak">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </summary>
                                            <div class="absolute right-0 mt-1 w-72 rounded-lg border border-gray-200 bg-white p-3 shadow-lg z-10">
                                                <form method="POST" action="{{ route('transaction.approval.reject', $currentStep->id) }}" onsubmit="var c = this.querySelector('textarea'); if (!c.value || c.value.trim().length < 10) { alert('Catatan penolakan wajib minimal 10 karakter.'); return false; } return confirm('Yakin menolak permintaan ini?');">
                                                    @csrf
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Catatan penolakan (min. 10 karakter)</label>
                                                    <textarea name="catatan" rows="3" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="Alasan penolakan..." required minlength="10"></textarea>
                                                    <div class="mt-2 flex gap-2 justify-end">
                                                        <button type="submit" class="px-3 py-1 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700">Kirim</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Tidak ada permintaan yang menunggu persetujuan Anda.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($paginator->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
@endsection