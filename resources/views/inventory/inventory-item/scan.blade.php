<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Scan QR - Rincian Aset</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; background: #f4f6f8; color: #1f2937; }
        .card { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
        h1 { margin: 0 0 6px; font-size: 24px; }
        h2 { margin: 18px 0 10px; font-size: 18px; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
        .muted { color: #6b7280; font-size: 13px; }
        .error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; margin-top: 8px; }
        .item { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
        .label { display: block; font-size: 12px; color: #6b7280; margin-bottom: 3px; }
        .value { font-size: 14px; font-weight: 700; color: #111827; word-break: break-word; }
        .actions { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { display: inline-block; text-decoration: none; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 700; }
        .btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
        .btn-secondary { background: #fff; color: #111827; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="card">
        <h1>Rincian Barangan</h1>

        @if($error)
            <div class="error">{{ $error }}</div>
            @if($kodeRegister)
                <div class="muted">Kode Registrasi: <strong>{{ $kodeRegister }}</strong></div>
            @endif
        @else
            @php($inv = $inventoryItem->inventory)
            <div class="muted">Kode Registrasi</div>
            <div style="font-size:20px;font-weight:800;margin-top:4px;">{{ $inventoryItem->kode_register }}</div>
            @if(auth()->check() && auth()->user()->hasRole('admin'))
                <div class="actions">
                    <a href="{{ route('inventory.inventory-item.edit', $inventoryItem->id_item) }}" class="btn btn-primary">Edit Data Item</a>
                    <a href="{{ route('inventory.data-inventory.edit', $inventoryItem->id_inventory) }}" class="btn btn-secondary">Edit Data Inventory</a>
                </div>
            @endif

            <h2>Informasi Barang</h2>
            <div class="grid">
                <div class="item"><span class="label">Data Barang</span><span class="value">{{ $inv->dataBarang->nama_barang ?? '-' }}</span></div>
                <div class="item"><span class="label">Jenis Inventory</span><span class="value">{{ $inv->jenis_inventory ?? '-' }}</span></div>
                <div class="item"><span class="label">Jenis Barang</span><span class="value">{{ $inv->jenis_barang ?? '-' }}</span></div>
                <div class="item"><span class="label">Qty</span><span class="value">{{ $qtyByKodeRegister ?? 1 }}</span></div>
                <div class="item"><span class="label">Harga</span><span class="value">Rp {{ number_format((float) ($inv->harga_satuan ?? 0), 0, ',', '.') }}</span></div>
                <div class="item"><span class="label">Tahun Anggaran</span><span class="value">{{ $inv->tahun_anggaran ?? '-' }}</span></div>
                <div class="item"><span class="label">Sumber Anggaran</span><span class="value">{{ $inv->sumberAnggaran->nama_anggaran ?? '-' }}</span></div>
            </div>

            <h2>Informasi Teknis</h2>
            <div class="grid">
                <div class="item"><span class="label">Merk</span><span class="value">{{ $inv->merk ?? '-' }}</span></div>
                <div class="item"><span class="label">Tipe</span><span class="value">{{ $inv->tipe ?? '-' }}</span></div>
                <div class="item"><span class="label">Nomor Seri</span><span class="value">{{ $inventoryItem->no_seri ?? $inv->no_seri ?? '-' }}</span></div>
                <div class="item"><span class="label">Spesifikasi</span><span class="value">{{ $inv->spesifikasi ?? '-' }}</span></div>
                <div class="item"><span class="label">Tahun Produksi</span><span class="value">{{ $inv->tahun_produksi ?? '-' }}</span></div>
                <div class="item"><span class="label">Nama Penyedia</span><span class="value">{{ $inv->nama_penyedia ?? '-' }}</span></div>
                <div class="item"><span class="label">Kondisi Barang</span><span class="value">{{ $inventoryItem->kondisi_item ?? '-' }}</span></div>
                
            </div>

            <h2>Section Lokasi</h2>
            <div class="grid">
                <div class="item"><span class="label">Unit Kerja</span><span class="value">{{ $inventoryItem->gudang->unitKerja->nama_unit_kerja ?? '-' }}</span></div>
                <div class="item"><span class="label">Ruangan</span><span class="value">{{ $inventoryItem->ruangan->nama_ruangan ?? '-' }}</span></div>
            </div>

            <h2>Foto / Gambar Barang</h2>
            <div class="grid">
                <div class="item"><span class="label">Foto Barang</span>
                    @if(!empty($fotoUrl))
                        <div style="margin-top:6px;">
                            <img src="{{ $fotoUrl }}" alt="Foto Barang" style="max-width:220px;max-height:220px;border:1px solid #d1d5db;border-radius:6px;">
                        </div>
                    @else
                        <span class="value">-</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</body>
</html>

