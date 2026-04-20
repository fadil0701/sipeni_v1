@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Proses Disposisi{{ $kategoriGudang ? ' - ' . $kategoriGudang : '' }}</h1>
        <p class="mt-1 text-sm text-gray-600">
            @if(($viewType ?? 'perlu_diproses') === 'riwayat')
                Riwayat disposisi yang sudah diproses{{ $kategoriGudang ? ' untuk kategori ' . $kategoriGudang : ' (Semua Kategori)' }}
            @else
                Daftar disposisi yang perlu diproses{{ $kategoriGudang ? ' untuk kategori ' . $kategoriGudang : ' (Semua Kategori)' }}
            @endif
        </p>
    </div>
    @if($isAdmin || ($isViewOnly ?? false))
    <div class="flex gap-2">
        <a href="{{ route('transaction.draft-distribusi.index', ['kategori' => 'ASET', 'view_type' => $viewType ?? 'perlu_diproses']) }}" 
           class="px-3 py-1.5 text-sm rounded-md {{ $kategoriGudang == 'ASET' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            ASET
        </a>
        <a href="{{ route('transaction.draft-distribusi.index', ['kategori' => 'PERSEDIAAN', 'view_type' => $viewType ?? 'perlu_diproses']) }}" 
           class="px-3 py-1.5 text-sm rounded-md {{ $kategoriGudang == 'PERSEDIAAN' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            PERSEDIAAN
        </a>
        <a href="{{ route('transaction.draft-distribusi.index', ['kategori' => 'FARMASI', 'view_type' => $viewType ?? 'perlu_diproses']) }}" 
           class="px-3 py-1.5 text-sm rounded-md {{ $kategoriGudang == 'FARMASI' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            FARMASI
        </a>
        <a href="{{ route('transaction.draft-distribusi.index', ['view_type' => $viewType ?? 'perlu_diproses']) }}" 
           class="px-3 py-1.5 text-sm rounded-md {{ !$kategoriGudang ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Semua
        </a>
    </div>
    @endif
</div>

<!-- Tab Navigation -->
<div class="mb-6 bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex" aria-label="Tabs">
            @php
                $currentViewType = $viewType ?? 'perlu_diproses';
                $baseParams = ['kategori' => $kategoriGudang];
            @endphp
            <a 
                href="{{ route('transaction.draft-distribusi.index', array_merge($baseParams, ['view_type' => 'perlu_diproses'])) }}" 
                class="px-6 py-3 text-sm font-medium {{ $currentViewType === 'perlu_diproses' ? 'border-b-2 border-blue-500 text-blue-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Perlu Diproses
            </a>
            <a 
                href="{{ route('transaction.draft-distribusi.index', array_merge($baseParams, ['view_type' => 'riwayat'])) }}" 
                class="px-6 py-3 text-sm font-medium {{ $currentViewType === 'riwayat' ? 'border-b-2 border-blue-500 text-blue-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Riwayat
            </a>
        </nav>
    </div>
</div>

<!-- Filters -->
@if(($viewType ?? 'perlu_diproses') === 'riwayat')
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('transaction.draft-distribusi.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="view_type" value="riwayat">
        @if($kategoriGudang)
            <input type="hidden" name="kategori" value="{{ $kategoriGudang }}">
        @endif
        
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
            @if(request('tanggal_mulai') || request('tanggal_akhir'))
                <a 
                    href="{{ route('transaction.draft-distribusi.index', array_merge($baseParams, ['view_type' => 'riwayat'])) }}" 
                    class="w-full px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 text-center"
                >
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>
@endif

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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Permintaan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($approvalLogs as $approvalLog)
                    @php
                        $permintaan = $approvalLog->permintaan;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $permintaan ? $permintaan->no_permintaan : '#' . $approvalLog->id_referensi }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                // Tentukan kategori dari role approval log
                                $kategori = null;
                                if ($approvalLog->approvalFlow && $approvalLog->approvalFlow->role) {
                                    $roleName = $approvalLog->approvalFlow->role->name;
                                    if ($roleName == 'admin_gudang_aset') {
                                        $kategori = 'ASET';
                                    } elseif ($roleName == 'admin_gudang_persediaan') {
                                        $kategori = 'PERSEDIAAN';
                                    } elseif ($roleName == 'admin_gudang_farmasi') {
                                        $kategori = 'FARMASI';
                                    }
                                }
                                $kategoriColor = match($kategori) {
                                    'ASET' => 'bg-blue-100 text-blue-800',
                                    'PERSEDIAAN' => 'bg-green-100 text-green-800',
                                    'FARMASI' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            @if($kategori)
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $kategoriColor }}">
                                    {{ $kategori }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $permintaan && $permintaan->unitKerja ? $permintaan->unitKerja->nama_unit_kerja : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $permintaan && $permintaan->pemohon ? $permintaan->pemohon->nama_pegawai : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $permintaan && $permintaan->tanggal_permintaan ? $permintaan->tanggal_permintaan->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($approvalLog->status) {
                                    'MENUNGGU' => 'bg-yellow-100 text-yellow-800',
                                    'DIPROSES' => 'bg-blue-100 text-blue-800',
                                    'DIDISPOSISIKAN' => 'bg-indigo-100 text-indigo-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusText = match($approvalLog->status) {
                                    'MENUNGGU' => 'Menunggu',
                                    'DIPROSES' => 'Diproses',
                                    'DIDISPOSISIKAN' => 'Didisposisikan',
                                    default => $approvalLog->status,
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $statusText }}
                            </span>
                            @if(($viewType ?? 'perlu_diproses') === 'riwayat' && $approvalLog->approved_at)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($approvalLog->approved_at)->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($approvalLog->status == 'MENUNGGU' && !($isViewOnly ?? false) && ($viewType ?? 'perlu_diproses') === 'perlu_diproses')
                                @php
                                    // Tentukan kategori untuk URL
                                    $kategoriParam = null;
                                    if ($approvalLog->approvalFlow && $approvalLog->approvalFlow->role) {
                                        $roleName = $approvalLog->approvalFlow->role->name;
                                        if ($roleName == 'admin_gudang_aset') {
                                            $kategoriParam = 'ASET';
                                        } elseif ($roleName == 'admin_gudang_persediaan') {
                                            $kategoriParam = 'PERSEDIAAN';
                                        } elseif ($roleName == 'admin_gudang_farmasi') {
                                            $kategoriParam = 'FARMASI';
                                        }
                                    }
                                @endphp
                                <a 
                                    href="{{ route('transaction.draft-distribusi.create', ['approvalLogId' => $approvalLog->id]) . ($kategoriParam ? '?kategori=' . $kategoriParam : '') }}" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                                    title="Proses"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Proses
                                </a>
                            @else
                                <a 
                                    href="{{ route('transaction.draft-distribusi.show', $approvalLog->id) }}" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-md hover:bg-indigo-200 transition-colors"
                                    title="Lihat Detail"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Lihat Detail
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(($viewType ?? 'perlu_diproses') === 'riwayat')
                                    Tidak ada riwayat disposisi{{ $kategoriGudang ? ' untuk kategori ' . $kategoriGudang : '' }}.
                                @else
                                    Tidak ada disposisi yang perlu diproses{{ $kategoriGudang ? ' untuk kategori ' . $kategoriGudang : '' }}.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($approvalLogs->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $approvalLogs->links() }}
        </div>
    @endif
</div>
@endsection

