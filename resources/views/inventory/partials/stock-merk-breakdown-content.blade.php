@php
    $qtyAgregat = (float) ($stock->qty_akhir ?? 0);
    $sum = (float) ($sumInventory ?? 0);
    $match = abs($qtyAgregat - $sum) < 0.0001;
@endphp

<div class="mb-4">
    <a href="{{ $backUrl }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">&larr; {{ $backLabel ?? 'Kembali' }}</a>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
    @if(!empty($pageSubtitle))
        <p class="mt-1 text-sm text-gray-600">{{ $pageSubtitle }}</p>
    @endif
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Barang</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $stock->dataBarang->nama_barang ?? '-' }}</dd>
            <dd class="text-xs text-gray-500">{{ $stock->dataBarang->kode_data_barang ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Gudang</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $stock->gudang->nama_gudang ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Satuan</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $stock->satuan->nama_satuan ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Stok agregat (Data Stok)</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($qtyAgregat, 2, ',', '.') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Jumlah dari inventory (per merk)</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($sum, 2, ',', '.') }}</dd>
        </div>
    </dl>
    @if(! $match)
        <p class="mt-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
            Total per merk berbeda dengan stok agregat. Sinkronisasi stok atau filter jenis barang dapat mempengaruhi angka; periksa data inventory dan penyesuaian stok.
        </p>
    @endif
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm font-semibold text-gray-800">Ringkasan per merk</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah baris inventory</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total qty</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($breakdownRows as $row)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $row->merk_label }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700">{{ $row->line_count }}</td>
                        <td class="px-6 py-3 text-sm text-right font-medium text-gray-900">{{ number_format((float) $row->qty_total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada baris inventory aktif yang memenuhi kriteria stok untuk kombinasi ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($breakdownRows->isNotEmpty())
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-800">Baris inventory (detail)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty stok masuk</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty stok keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kedaluwarsa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($breakdownRows as $row)
                        @foreach($row->lines as $line)
                            <tr>
                                <td class="px-6 py-2 text-sm text-gray-600">{{ $line->id_inventory }}</td>
                                <td class="px-6 py-2 text-sm text-gray-900">{{ trim((string) $line->merk) !== '' ? $line->merk : '(Tanpa merk)' }}</td>
                                <td class="px-6 py-2 text-sm text-right text-gray-900">{{ number_format((float) $line->qty_input, 2, ',', '.') }}</td>
                                <td class="px-6 py-2 text-sm text-right text-gray-900">{{ number_format((float) ($line->qty_stock_masuk ?? 0), 2, ',', '.') }}</td>
                                <td class="px-6 py-2 text-sm text-right text-gray-900">{{ number_format((float) ($line->qty_stock_keluar ?? 0), 2, ',', '.') }}</td>
                                <td class="px-6 py-2 text-sm text-gray-700">{{ $line->no_batch ?: '-' }}</td>
                                <td class="px-6 py-2 text-sm text-gray-700">
                                    {{ $line->tanggal_kedaluwarsa ? \Carbon\Carbon::parse($line->tanggal_kedaluwarsa)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-2 text-sm text-gray-700">{{ $line->jenis_inventory ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="px-6 py-3 text-xs text-gray-500 border-t border-gray-100 bg-gray-50">
            Qty stok masuk: jumlah dari penerimaan barang berstatus DITERIMA. Qty stok keluar: jumlah dari distribusi selesai dan pemakaian barang berstatus DISETUJUI (per baris inventory).
        </p>
    </div>
@endif
