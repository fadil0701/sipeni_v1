@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Analytics</h1>
        <p class="mt-1 text-sm text-gray-500">Ringkasan aktivitas distribusi barang, stok, dan aset terbaru.</p>
    </div>
    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-medium text-blue-700">
        Last update: {{ now()->format('d M Y H:i') }}
    </div>
</div>

@if($isPengurusBarangWorkspace ?? false)
@php
    $currentUser = auth()->user();
@endphp
<div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-blue-900">Workspace Pengurus Barang</h2>
            <p class="mt-1 text-sm text-blue-700">Ringkasan kerja harian dan aksi cepat operasional barang.</p>
        </div>
        <div class="inline-flex rounded-md border border-blue-200 bg-white p-1 text-xs">
            <a
                href="{{ route('user.dashboard', array_merge(request()->query(), ['workspace_scope' => 'all'])) }}"
                class="rounded px-2 py-1 font-medium {{ ($workspaceScope ?? 'all') === 'all' ? 'bg-blue-600 text-white' : 'text-blue-700 hover:bg-blue-50' }}"
            >
                Semua item role
            </a>
            <a
                href="{{ route('user.dashboard', array_merge(request()->query(), ['workspace_scope' => 'my'])) }}"
                class="rounded px-2 py-1 font-medium {{ ($workspaceScope ?? 'all') === 'my' ? 'bg-blue-600 text-white' : 'text-blue-700 hover:bg-blue-50' }}"
            >
                Hanya item saya
            </a>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-lg bg-white p-3 shadow-sm">
            <p class="text-xs text-gray-500">Approval Menunggu</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($workspaceStats['approval_perlu_diproses'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm">
            <p class="text-xs text-gray-500">Draft Distribusi</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($workspaceStats['draft_distribusi'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm">
            <p class="text-xs text-gray-500">Distribusi Diproses</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($workspaceStats['distribusi_perlu_proses'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm">
            <p class="text-xs text-gray-500">Perlu Penerimaan</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($workspaceStats['distribusi_perlu_diterima'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm">
            <p class="text-xs text-gray-500">Retur Diajukan</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($workspaceStats['retur_diajukan'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm">
            <p class="text-xs text-gray-500">Pemakaian Diajukan</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ number_format($workspaceStats['pemakaian_diajukan'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-2">
        @if(\App\Helpers\PermissionHelper::canAccess($currentUser, 'transaction.approval.index'))
            <a href="{{ route('transaction.approval.index') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-700">Buka Approval</a>
        @endif
        @if(\App\Helpers\PermissionHelper::canAccess($currentUser, 'transaction.draft-distribusi.index'))
            <a href="{{ route('transaction.draft-distribusi.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">Draft Distribusi</a>
        @endif
        @if(\App\Helpers\PermissionHelper::canAccess($currentUser, 'transaction.distribusi.index'))
            <a href="{{ route('transaction.distribusi.index') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700">Distribusi (SBBK)</a>
        @endif
        @if(\App\Helpers\PermissionHelper::canAccess($currentUser, 'transaction.penerimaan-barang.index'))
            <a href="{{ route('transaction.penerimaan-barang.index') }}" class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-xs font-medium text-white hover:bg-cyan-700">Penerimaan Barang</a>
        @endif
        @if(\App\Helpers\PermissionHelper::canAccess($currentUser, 'transaction.retur-barang.index'))
            <a href="{{ route('transaction.retur-barang.index') }}" class="inline-flex items-center rounded-md bg-amber-600 px-3 py-2 text-xs font-medium text-white hover:bg-amber-700">Retur Barang</a>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('transaction.pemakaian-barang.index') && \App\Helpers\PermissionHelper::canAccess($currentUser, 'transaction.pemakaian-barang.index'))
            <a href="{{ route('transaction.pemakaian-barang.index') }}" class="inline-flex items-center rounded-md bg-fuchsia-600 px-3 py-2 text-xs font-medium text-white hover:bg-fuchsia-700">Pemakaian Barang</a>
        @endif
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-blue-100 bg-white p-4">
            <h3 class="text-sm font-semibold text-gray-900">Antrian Prioritas</h3>
            <p class="mt-1 text-xs text-gray-500">Top 5 item paling urgent berdasarkan usia antrian.</p>
            <div class="mt-3 space-y-2">
                @forelse(($urgentQueue ?? collect()) as $row)
                    @php
                        $statusClass = match($row['status']) {
                            'MENUNGGU', 'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                            'DIPROSES' => 'bg-blue-100 text-blue-800',
                            'DIKIRIM' => 'bg-indigo-100 text-indigo-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <a href="{{ $row['url'] }}" class="flex items-center justify-between rounded-md border border-gray-100 px-3 py-2 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $row['title'] }}</p>
                            <p class="text-xs text-gray-500">{{ $row['subtitle'] }} · {{ $row['age_hours'] }} jam</p>
                        </div>
                        <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-semibold {{ $statusClass }}">
                            {{ $row['status'] }}
                        </span>
                    </a>
                @empty
                    <div class="rounded-md border border-dashed border-gray-200 px-3 py-4 text-center text-xs text-gray-500">
                        Tidak ada antrian prioritas saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-blue-100 bg-white p-4">
            <h3 class="text-sm font-semibold text-gray-900">SLA Monitoring</h3>
            <p class="mt-1 text-xs text-gray-500">Jumlah item melewati batas SLA sederhana.</p>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div class="rounded-md bg-red-50 p-3">
                    <p class="text-[11px] text-red-700">Approval &gt; 24 jam</p>
                    <p class="mt-1 text-xl font-semibold text-red-800">{{ $workspaceSla['approval_over_sla'] ?? 0 }}</p>
                </div>
                <div class="rounded-md bg-orange-50 p-3">
                    <p class="text-[11px] text-orange-700">Distribusi &gt; 48 jam</p>
                    <p class="mt-1 text-xl font-semibold text-orange-800">{{ $workspaceSla['distribusi_over_sla'] ?? 0 }}</p>
                </div>
                <div class="rounded-md bg-amber-50 p-3">
                    <p class="text-[11px] text-amber-700">Retur &gt; 72 jam</p>
                    <p class="mt-1 text-xl font-semibold text-amber-800">{{ $workspaceSla['retur_over_sla'] ?? 0 }}</p>
                </div>
                <div class="rounded-md bg-yellow-50 p-3">
                    <p class="text-[11px] text-yellow-700">Pemakaian &gt; 48 jam</p>
                    <p class="mt-1 text-xl font-semibold text-yellow-800">{{ $workspaceSla['pemakaian_over_sla'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-6 mb-6">
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Total Aset</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($totalAssets, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Unit terdaftar</p>
            </div>
            <div class="rounded-lg bg-indigo-100 p-3 text-indigo-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Nilai Persediaan</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">Rp {{ number_format($totalPersediaanValue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Akumulasi nilai inventory persediaan</p>
            </div>
            <div class="rounded-lg bg-emerald-100 p-3 text-emerald-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 5 9-5-9-5-9 5zm0 5l9 5 9-5M3 17l9 5 9-5"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Nilai Aset</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Akumulasi nilai inventory aset</p>
            </div>
            <div class="rounded-lg bg-cyan-100 p-3 text-cyan-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1a4.978 4.978 0 01-2.121-.475M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Nilai Farmasi</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">Rp {{ number_format($totalFarmasiValue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Akumulasi nilai inventory farmasi</p>
            </div>
            <div class="rounded-lg bg-teal-100 p-3 text-teal-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656 0L12 17.2l-1.772-1.772a4 4 0 10-5.656 5.656L12 28.312l7.428-7.228a4 4 0 000-5.656z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Permintaan Aktif</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($activeRequests, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Perlu tindak lanjut</p>
            </div>
            <div class="rounded-lg bg-amber-100 p-3 text-amber-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Aktivitas Terbaru</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($latestTransactions->count(), 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Distribusi + penerimaan</p>
            </div>
            <div class="rounded-lg bg-rose-100 p-3 text-rose-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 13l3-3 3 2 4-5"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2 mb-6">
    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Komposisi Inventory per Kategori</h3>
                <p class="text-sm text-gray-500">Aset, persediaan, dan farmasi</p>
            </div>
        </div>
        <div class="h-72">
            <canvas id="inventoryCategoryChart"></canvas>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">Tracking Distribusi</h3>
            <p class="text-sm text-gray-500">Pantau status distribusi barang terbaru</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($latestDistribusiTracking as $item)
                @php
                    $badgeColor = match($item['status']) {
                        'SELESAI' => 'bg-emerald-100 text-emerald-800',
                        'DIKIRIM' => 'bg-indigo-100 text-indigo-800',
                        'DIPROSES' => 'bg-amber-100 text-amber-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <div class="px-6 py-4">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $item['no_sbbk'] }}</p>
                            <p class="text-xs text-gray-500">{{ $item['tujuan'] }} · {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeColor }}">
                            {{ $item['status'] }}
                        </span>
                    </div>
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-600">Progress Distribusi</span>
                        <span class="font-semibold text-gray-700">{{ $item['progress_percent'] }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-indigo-600 transition-all" style="width: {{ $item['progress_percent'] }}%"></div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">
                    Belum ada data tracking distribusi.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="mb-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Status Permintaan Barang</h3>
            <p class="text-sm text-gray-500">Distribusi status pengajuan saat ini</p>
        </div>
    </div>
    <div class="h-72">
        <canvas id="requestStatusChart"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2 rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Tracking Permintaan - Approval</h3>
                <p class="text-sm text-gray-500">Pantau posisi proses approval permintaan terbaru</p>
            </div>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($trackingItems as $item)
                @php
                    $badgeColor = match($item['status']) {
                        'MENUNGGU', 'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                        'DIKETAHUI' => 'bg-blue-100 text-blue-800',
                        'DIVERIFIKASI', 'DISETUJUI' => 'bg-green-100 text-green-800',
                        'DIDISPOSISIKAN', 'DIPROSES' => 'bg-indigo-100 text-indigo-800',
                        'DITOLAK' => 'bg-red-100 text-red-800',
                        'SELESAI', 'DITERIMA' => 'bg-emerald-100 text-emerald-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <div class="px-6 py-4">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $item['no_permintaan'] }}</p>
                            <p class="text-xs text-gray-500">{{ $item['pemohon'] }} · {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeColor }}">
                            {{ $item['status'] }}
                        </span>
                    </div>
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-600">Step {{ $item['step_order'] }} / {{ $trackingStepMax }} - {{ $item['step_name'] }}</span>
                        <span class="font-semibold text-gray-700">{{ $item['progress_percent'] }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-blue-600 transition-all" style="width: {{ $item['progress_percent'] }}%"></div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">
                    Belum ada data tracking permintaan.
                </div>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">Permintaan Terbaru</h3>
            <p class="text-sm text-gray-500">5 permintaan terakhir dari unit kerja</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($latestRequests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $request->no_permintaan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $request->pemohon->nama_pegawai ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($request->tanggal_permintaan)->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada permintaan terbaru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">Aset Terbaru</h3>
            <p class="text-sm text-gray-500">5 inventaris aset terbaru</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Aset</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kondisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($latestAssets as $asset)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $asset->inventory->dataBarang->nama_barang ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $asset->kode_register ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $status = $asset->kondisi_item ?? 'N/A';
                                $color = match($status) {
                                    'BAIK' => 'bg-green-100 text-green-700',
                                    'RUSAK_RINGAN' => 'bg-yellow-100 text-yellow-700',
                                    'RUSAK_BERAT' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $color }}">{{ $status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada aset terbaru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('requestStatusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Diajukan', 'Disetujui', 'Dikirim', 'Ditolak'],
                datasets: [{
                    label: 'Jumlah Permintaan',
                    data: [
                        {{ $requestStatusData['diajukan'] }},
                        {{ $requestStatusData['disetujui'] }},
                        {{ $requestStatusData['dikirim'] }},
                        {{ $requestStatusData['ditolak'] }}
                    ],
                    backgroundColor: ['#696CFF', '#71DD37', '#FFAB00', '#FF3E1D'],
                    borderRadius: 6,
                    maxBarThickness: 36,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: '#f1f1f4'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    const inventoryCtx = document.getElementById('inventoryCategoryChart');
    if (inventoryCtx) {
        new Chart(inventoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Aset', 'Persediaan', 'Farmasi'],
                datasets: [{
                    data: [
                        {{ $inventoryCategoryData['aset'] }},
                        {{ $inventoryCategoryData['persediaan'] }},
                        {{ $inventoryCategoryData['farmasi'] }}
                    ],
                    backgroundColor: ['#696CFF', '#71DD37', '#03C3EC'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
