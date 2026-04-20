@extends('layouts.app')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <!-- Total Aset -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Aset</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ number_format($totalAssets, 0, ',', '.') }} Unit</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Gudang -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Stok Gudang</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ number_format($totalStock, 0, ',', '.') }} Item</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Permintaan Aktif -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Permintaan Aktif</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $activeRequests }} Pengajuan</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit / BMD -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Audit / BMD</dt>
                        <dd class="text-lg font-semibold text-gray-900">0 Jadwal Audit</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flowchart and Chart Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Alur Distribusi Barang -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Alur Distribusi Barang</h3>
        <p class="text-sm text-gray-500 mb-4">Proses distribusi barang dari gudang pusat ke unit</p>
        
        <div class="flex flex-col items-center space-y-4">
            <!-- First Row: Gudang Pusat -> Gudang Unit -->
            <div class="flex items-center justify-center space-x-4 w-full">
                <div class="bg-gray-100 rounded-lg p-4 text-center min-w-[130px] border border-gray-200">
                    <div class="text-2xl mb-2">📦</div>
                    <div class="font-semibold text-gray-900 text-sm">Gudang Pusat</div>
                </div>

                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>

                <div class="bg-blue-100 rounded-lg p-4 text-center min-w-[130px] border border-blue-200">
                    <div class="text-2xl mb-2">🏢</div>
                    <div class="font-semibold text-gray-900 text-sm">Gudang Unit</div>
                </div>
            </div>

            <!-- Arrow Down -->
            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>

            <!-- Second Row: Gudang Unit -> Kirim ke Ruangan -> Pemakaian -->
            <div class="flex flex-col items-center space-y-4 w-full">
                <div class="flex items-center justify-center space-x-4 w-full">
                    <div class="bg-green-100 rounded-lg p-4 text-center min-w-[130px] border border-green-200">
                        <div class="text-2xl mb-2">🚪</div>
                        <div class="font-semibold text-gray-900 text-sm">Kirim ke Ruangan</div>
                    </div>

                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>

                    <div class="bg-blue-600 rounded-lg p-4 text-center min-w-[130px] text-white shadow-sm">
                        <div class="text-2xl mb-2">💼</div>
                        <div class="font-semibold text-sm">Pemakaian</div>
                    </div>
                </div>

                <!-- Arrow Down (from Gudang Unit to Pemakaian directly) -->
                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>

                <!-- Direct Pemakaian from Gudang Unit -->
                <div class="bg-blue-600 rounded-lg p-4 text-center min-w-[130px] text-white shadow-sm">
                    <div class="text-2xl mb-2">💼</div>
                    <div class="font-semibold text-sm">Pemakaian Langsung</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Permintaan Barang Chart -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Permintaan Barang</h3>
        <p class="text-sm text-gray-500 mb-4">Grafik status permintaan barang</p>
        <div class="h-64">
            <canvas id="requestStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Inventaris Aset Terbaru -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventaris Aset Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama Aset</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Kode Aset</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($latestAssets as $asset)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $asset->inventory->dataBarang->nama_barang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $asset->kode_register ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $asset->ruangan->nama_ruangan ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $status = $asset->kondisi_item ?? 'N/A';
                                        $color = match($status) {
                                            'BAIK' => 'bg-green-100 text-green-800',
                                            'RUSAK_RINGAN' => 'bg-yellow-100 text-yellow-800',
                                            'RUSAK_BERAT' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                        {{ $status === 'BAIK' ? 'Baik' : $status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Belum ada data aset
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi Terakhir -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Transaksi Terakhir</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jenis Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($latestTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction['id'] }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $color = match($transaction['jenis']) {
                                            'Distribusi (SBBK)' => 'bg-blue-100 text-blue-800',
                                            'Penerimaan' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                        {{ $transaction['jenis'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($transaction['tanggal'])->format('Y-m-d') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Belum ada data transaksi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                    backgroundColor: [
                        'rgb(59, 130, 246)', // Blue
                        'rgb(34, 197, 94)',  // Green
                        'rgb(249, 115, 22)', // Orange
                        'rgb(239, 68, 68)',  // Red
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(249, 115, 22)',
                        'rgb(239, 68, 68)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 750
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
