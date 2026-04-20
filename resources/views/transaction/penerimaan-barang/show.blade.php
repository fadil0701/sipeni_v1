@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.penerimaan-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Penerimaan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Penerimaan Barang</h2>
            <p class="text-sm text-gray-600 mt-1">No. Penerimaan: <span class="font-semibold">{{ $penerimaan->no_penerimaan }}</span></p>
        </div>
        <div class="flex space-x-3">
            @php
                $user = auth()->user();
            @endphp
            @if(\App\Helpers\PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.edit'))
            <a 
                href="{{ route('transaction.penerimaan-barang.edit', $penerimaan->id_penerimaan) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            @endif
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Penerimaan</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Penerimaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->no_penerimaan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $statusColor = match($penerimaan->status_penerimaan) {
                                    'DITERIMA' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $penerimaan->status_penerimaan }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. SBBK</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('transaction.distribusi.show', $penerimaan->distribusi->id_distribusi) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $penerimaan->distribusi->no_sbbk ?? '-' }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pegawai Penerima</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->pegawaiPenerima->nama_pegawai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Penerimaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->tanggal_penerimaan->format('d/m/Y') }}</dd>
                    </div>
                    @if($penerimaan->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $penerimaan->keterangan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Detail Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Penerimaan ({{ $penerimaan->detailPenerimaan->count() }} item)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Dikirim</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Diterima</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                @php
                                    $hasFarmasiPersediaan = $penerimaan->detailPenerimaan->filter(function($detail) {
                                        $kategoriGudang = $detail->inventory->gudang->kategori_gudang ?? null;
                                        return in_array($kategoriGudang, ['FARMASI', 'PERSEDIAAN']);
                                    })->count() > 0;
                                    $hasAset = $penerimaan->detailPenerimaan->filter(function($detail) {
                                        $kategoriGudang = $detail->inventory->gudang->kategori_gudang ?? null;
                                        return $kategoriGudang === 'ASET';
                                    })->count() > 0;
                                @endphp
                                @if($hasFarmasiPersediaan)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Batch</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exp Date</th>
                                @endif
                                @if($hasAset)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Seri</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($penerimaan->detailPenerimaan as $index => $detail)
                            @php
                                // Cari detail distribusi yang sesuai berdasarkan id_inventory
                                $detailDistribusi = $penerimaan->distribusi->detailDistribusi->firstWhere('id_inventory', $detail->id_inventory);
                                $qtyDikirim = $detailDistribusi ? $detailDistribusi->qty_distribusi : 0;
                                $qtyDiterima = $detail->qty_diterima ?? 0;
                                
                                $inventory = $detail->inventory;
                                $kategoriGudang = $inventory->gudang->kategori_gudang ?? null;
                                $isAset = $kategoriGudang === 'ASET';
                                $isFarmasiPersediaan = in_array($kategoriGudang, ['FARMASI', 'PERSEDIAAN']);
                                
                                // Untuk ASET, ambil nomor seri dari inventory_item
                                $noSeriList = [];
                                if ($isAset) {
                                    $inventoryItems = \App\Models\InventoryItem::where('id_inventory', $inventory->id_inventory)
                                        ->where('id_gudang', $inventory->id_gudang)
                                        ->where('status_item', 'AKTIF')
                                        ->limit((int)$qtyDiterima)
                                        ->get();
                                    $noSeriList = $inventoryItems->pluck('no_seri')->filter()->unique()->values();
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $inventory->dataBarang->nama_barang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $inventory->jenis_barang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ number_format($qtyDikirim, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ number_format($qtyDiterima, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                @if($hasFarmasiPersediaan)
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($isFarmasiPersediaan)
                                        {{ $inventory->no_batch ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($isFarmasiPersediaan && $inventory->tanggal_kedaluwarsa)
                                        {{ \Carbon\Carbon::parse($inventory->tanggal_kedaluwarsa)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @endif
                                @if($hasAset)
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($isAset)
                                        @if($noSeriList->count() > 0)
                                            @if($noSeriList->count() <= 3)
                                                {{ $noSeriList->join(', ') }}
                                            @else
                                                {{ $noSeriList->take(3)->join(', ') }}<br>
                                                <span class="text-xs text-gray-500">+{{ $noSeriList->count() - 3 }} lainnya</span>
                                            @endif
                                        @else
                                            {{ $inventory->no_seri ?? '-' }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                @php
                                    $colspan = 7;
                                    if ($hasFarmasiPersediaan) $colspan += 2;
                                    if ($hasAset) $colspan += 1;
                                @endphp
                                <td colspan="{{ $colspan }}" class="px-4 py-3 text-sm text-center text-gray-500">Tidak ada data detail penerimaan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

