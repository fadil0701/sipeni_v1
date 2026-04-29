@extends('layouts.app')

@section('content')
@php
    $statusValue = $distribusi->status_distribusi instanceof \App\Enums\DistribusiStatus
        ? $distribusi->status_distribusi->value
        : $distribusi->status_distribusi;
    $hasPegawaiPengirim = (bool) $distribusi->id_pegawai_pengirim;
@endphp

<div class="mb-4">
    <a href="{{ route('transaction.distribusi.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Distribusi
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded text-sm text-red-800">{{ session('error') }}</div>
@endif
@if(session('info'))
    <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded text-sm text-blue-800">{{ session('info') }}</div>
@endif

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Distribusi Barang (SBBK)</h2>
            <p class="text-sm text-gray-600 mt-1">No. SBBK: <span class="font-semibold">{{ $distribusi->no_sbbk }}</span></p>
            <p class="text-xs text-gray-500 mt-1">Lanjutkan alur dengan tombol di kanan: ubah data (draft), proses, lalu kirim setelah pegawai pengirim diisi.</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if($statusValue === 'draft')
                <a
                    href="{{ route('transaction.distribusi.edit', $distribusi->id_distribusi) }}"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-100 rounded-md hover:bg-amber-200"
                >
                    Edit
                </a>
                <form method="POST" action="{{ route('transaction.distribusi.proses', $distribusi->id_distribusi) }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-md hover:bg-indigo-200"
                        data-confirm="Proses distribusi ini sekarang?"
                    >
                        Proses
                    </button>
                </form>
            @endif
            @if($statusValue === 'diproses')
                <a
                    href="{{ route('transaction.distribusi.edit', $distribusi->id_distribusi) }}"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-100 rounded-md hover:bg-amber-200"
                >
                    Edit
                </a>
                <form method="POST" action="{{ route('transaction.distribusi.kirim', $distribusi->id_distribusi) }}" class="inline" data-confirm="Kirim distribusi ini sekarang?" onsubmit="if (!{{ $hasPegawaiPengirim ? 'true' : 'false' }}) { alert('Pilih pegawai pengirim terlebih dahulu pada menu Edit.'); return false; } return true;">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200"
                    >
                        Kirim
                    </button>
                </form>
            @endif
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. SBBK</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->no_sbbk }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $statusColor = match($statusValue) {
                                    'dikirim' => 'bg-blue-100 text-blue-800',
                                    'selesai' => 'bg-green-100 text-green-800',
                                    'diproses' => 'bg-indigo-100 text-indigo-800',
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $statusValue }}
                            </span>
                        </dd>
                    </div>
                    @if($distribusi->permintaan)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Permintaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('transaction.permintaan-barang.show', $distribusi->permintaan->id_permintaan) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $distribusi->permintaan->no_permintaan }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja Pemohon</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->permintaan->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Distribusi</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->tanggal_distribusi->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang Asal</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                // Ambil semua gudang asal dari detail distribusi (karena bisa berbeda per item)
                                $gudangAsalList = $distribusi->detailDistribusi->map(function($detail) {
                                    if ($detail->inventory && $detail->inventory->gudang) {
                                        return $detail->inventory->gudang->nama_gudang;
                                    }
                                    return null;
                                })->filter()->unique()->values();
                                
                                // Jika semua dari gudang yang sama, tampilkan satu
                                // Jika berbeda, tampilkan semua
                                if ($gudangAsalList->count() == 1) {
                                    echo $gudangAsalList->first();
                                } elseif ($gudangAsalList->count() > 1) {
                                    echo $gudangAsalList->join(', ');
                                    echo ' <span class="text-xs text-gray-500">(' . $gudangAsalList->count() . ' gudang)</span>';
                                } else {
                                    // Fallback ke gudang asal dari distribusi
                                    echo $distribusi->gudangAsal->nama_gudang ?? '-';
                                }
                            @endphp
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang Tujuan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->gudangTujuan->nama_gudang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pegawai Pengirim</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->pegawaiPengirim->nama_pegawai ?? '-' }}</dd>
                    </div>
                    @if($distribusi->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $distribusi->keterangan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Detail Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Distribusi ({{ $distribusi->detailDistribusi->count() }} item)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gudang Asal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Distribusi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                @if($distribusi->detailDistribusi->whereIn('inventory.gudang.kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Batch</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exp Date</th>
                                @endif
                                @if($distribusi->detailDistribusi->where('inventory.gudang.kategori_gudang', 'ASET')->count() > 0)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Seri</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php 
                                $total = 0;
                                $hasFarmasiPersediaan = $distribusi->detailDistribusi->whereIn('inventory.gudang.kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0;
                                $hasAset = $distribusi->detailDistribusi->where('inventory.gudang.kategori_gudang', 'ASET')->count() > 0;
                            @endphp
                            @foreach($distribusi->detailDistribusi as $index => $detail)
                            @php 
                                $total += $detail->subtotal;
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
                                        ->limit((int)$detail->qty_distribusi)
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
                                    {{ $inventory->gudang->nama_gudang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($detail->qty_distribusi, 2, ',', '.') }}</td>
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
                                <td class="px-4 py-3 text-sm text-gray-900">Rp {{ number_format($detail->harga_satuan, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">Rp {{ number_format($detail->subtotal, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        {{-- Total di tfoot: enhanceTable (layouts/app) hanya mem-sort tbody.rows, bukan tfoot. --}}
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold border-t border-gray-200">
                                @php
                                    $colspanLabel = 6 + ($hasFarmasiPersediaan ? 2 : 0) + ($hasAset ? 1 : 0);
                                @endphp
                                <td colspan="{{ $colspanLabel }}" class="px-4 py-3 text-sm text-gray-900 text-right">Total</td>
                                <td class="px-4 py-3 text-sm text-gray-900"></td>
                                <td class="px-4 py-3 text-sm text-gray-900">Rp {{ number_format($total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

