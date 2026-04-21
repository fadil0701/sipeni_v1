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

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4 mb-6">
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
                <p class="text-xs uppercase tracking-wide text-gray-500">Stok Gudang</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($totalStock, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-500">Qty akhir</p>
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

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3 mb-6">
    <div class="xl:col-span-2 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
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

    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-gray-900">Quick Summary</h3>
        <p class="mt-1 text-sm text-gray-500">Ikhtisar status permintaan</p>
        <div class="mt-5 space-y-4">
            <div>
                <div class="mb-1 flex justify-between text-sm"><span class="text-gray-600">Diajukan</span><span class="font-medium">{{ $requestStatusData['diajukan'] }}</span></div>
                <div class="h-2 rounded bg-blue-100"><div class="h-2 rounded bg-blue-500" style="width: {{ max(8, min(100, $requestStatusData['diajukan'] * 10)) }}%"></div></div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm"><span class="text-gray-600">Disetujui</span><span class="font-medium">{{ $requestStatusData['disetujui'] }}</span></div>
                <div class="h-2 rounded bg-green-100"><div class="h-2 rounded bg-green-500" style="width: {{ max(8, min(100, $requestStatusData['disetujui'] * 10)) }}%"></div></div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm"><span class="text-gray-600">Dikirim</span><span class="font-medium">{{ $requestStatusData['dikirim'] }}</span></div>
                <div class="h-2 rounded bg-orange-100"><div class="h-2 rounded bg-orange-500" style="width: {{ max(8, min(100, $requestStatusData['dikirim'] * 10)) }}%"></div></div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm"><span class="text-gray-600">Ditolak</span><span class="font-medium">{{ $requestStatusData['ditolak'] }}</span></div>
                <div class="h-2 rounded bg-red-100"><div class="h-2 rounded bg-red-500" style="width: {{ max(8, min(100, $requestStatusData['ditolak'] * 10)) }}%"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">Tracking Permintaan - Approval</h3>
            <p class="text-sm text-gray-500">Pantau posisi proses approval permintaan terbaru</p>
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
</script>
@endpush
@endsection
