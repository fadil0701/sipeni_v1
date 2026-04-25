<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen KIR - {{ $unitKerja->nama_unit_kerja }}</title>
    <style>
        @page {
            @if(!empty($downloadMode))
            /* Officio/F4 (8.5 x 13 inch) untuk file download */
            size: 13in 8.5in;
            margin: 8mm;
            @else
            /* Mode cetak langsung: dinamis mengikuti printer/user setting */
            margin: 8mm;
            @endif
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #111;
            background: #f5f5f5;
            font-size: 10px;
        }
        .toolbar {
            max-width: 1400px;
            margin: 12px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding: 0 10px;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            border: 1px solid #c7c7c7;
            background: #fff;
            color: #111;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
        }
        .btn-primary {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
        }
        .kir-paper {
            width: 100%;
            max-width: 100%;
            margin: 10px auto;
            background: #fff;
            padding: 10px;
            border: 1px solid #d1d5db;
        }
        .kir-header {
            display: grid;
            grid-template-columns: 1.45fr 1fr 1fr;
            gap: 8px;
            align-items: start;
        }
        .kir-header .center {
            text-align: center;
            font-weight: 700;
            line-height: 1.2;
        }
        .kir-header .line { margin-bottom: 2px; }
        .kir-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin-top: 6px;
        }
        .kir-table th,
        .kir-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: top;
            white-space: normal;
            word-break: normal;
            overflow-wrap: anywhere;
            line-height: 1.2;
        }
        .kir-table th {
            text-align: center;
            font-weight: 700;
            font-size: 9px;
        }
        .kir-table td { font-size: 9px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .kir-sign {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            text-align: center;
            font-size: 10px;
        }
        .kir-sign .name-space {
            margin-top: 36px;
            text-decoration: underline;
            min-height: 12px;
        }
        @media print {
            body { background: #fff; }
            .print-hidden { display: none !important; }
            .kir-paper {
                border: none;
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
            .kir-paper:last-child { page-break-after: auto; }
            .kir-table th,
            .kir-table td {
                font-size: 8.5px;
            }
        }
    </style>
</head>
<body>
@php
    $rowsByRuangan = $rows->groupBy(function ($row) {
        return $row->ruangan?->id_ruangan ?? 0;
    });
@endphp

<div class="toolbar print-hidden">
    <a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="btn">Kembali ke Daftar Dokumen KIR</a>
    <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen</button>
</div>

@forelse($rowsByRuangan as $groupRows)
    @php
        $first = $groupRows->first();
        $ruangan = $first?->ruangan;
        $tahun = now()->format('Y');
    @endphp
    <div class="kir-paper">
    
        <div class="kir-header">
            <div>
                <div class="line"><strong>PROVINSI</strong> : PEMERINTAH PROVINSI DKI JAKARTA</div>
                <div class="line"><strong>KABUPATEN/KOTA</strong> : Wilayah Unit Kerja</div>
                <div class="line"><strong>UNIT KERJA</strong> : {{ $unitKerja->nama_unit_kerja }}</div>
                <div class="line"><strong>SATUAN KERJA</strong> : PUSAT PELAYANAN KESEHATAN PEGAWAI PROV. DKI JAKARTA</div>
            </div>
            <div class="center">
                <div>KARTU INVENTARIS RUANGAN</div>
                <div>TAHUN {{ $tahun }}</div>
            </div>
            <div>
                <div class="line" ><strong>KODE UNIT KERJA</strong> : {{ $unitKerja->id_unit_kerja }}</div>
                <div class="line"><strong>RUANGAN</strong> : {{ $ruangan?->nama_ruangan ?? '-' }}</div>
            </div>
        </div>

        <table class="kir-table">
            <thead>
            <tr>
                <th style="width:28px;">NO</th>
                <th style="width:90px;">KODE DATA BARANG</th>
                <th style="width:120px;">KODE REGISTER</th>
                <th style="min-width:150px;">NAMA BARANG</th>
                <th style="min-width:110px;">MERK / MODEL</th>
                <th style="width:80px;">JENIS BARANG</th>
                <th style="width:80px;">NO.SERI</th>
                <th style="width:65px;">TAHUN PEMBUATAN</th>
                <th style="width:65px;">TAHUN PEMBELIAN</th>
                <th style="width:60px;">JUMLAH BARANG</th>
                <th style="width:55px;">SATUAN</th>
                <th style="width:80px;">HARGA SATUAN</th>
                <th style="width:75px;">KEADAAN BARANG</th>
                <th style="width:80px;">TANGGAL PEMELIHARAAN</th>
                <th style="width:80px;">TANGGAL KALIBRASI</th>
                <th style="min-width:100px;">KETERANGAN / MUTASI / DLL</th>
            </tr>
            </thead>
            <tbody>
            @foreach($groupRows as $row)
                @php
                    $register = $row->registerAset;
                    $inventory = $register?->inventory;
                    $inventoryItem = $register?->inventoryItem;
                    $dataBarang = $inventory?->dataBarang;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $dataBarang?->kode_data_barang ?? '-' }}</td>
                    <td>{{ $register?->nomor_register ?? '-' }}</td>
                    <td>{{ $dataBarang?->nama_barang ?? '-' }}</td>
                    <td>{{ $inventory?->merk ?? '-' }}</td>
                    <td>{{ $inventory?->jenis_barang ?? '-' }}</td>
                    <td>{{ $inventoryItem?->no_seri ?? ($inventory?->no_seri ?? '-') }}</td>
                    <td class="text-center">{{ $inventory?->tahun_produksi ?? '-' }}</td>
                    <td class="text-center">{{ $inventory?->tahun_anggaran ?? '-' }}</td>
                    <td class="text-center">1</td>
                    <td>{{ $inventory?->satuan?->nama_satuan ?? '-' }}</td>
                    <td class="text-right">{{ $inventory?->harga_satuan ? number_format((float) $inventory->harga_satuan, 0, ',', '.') : '-' }}</td>
                    <td>{{ $register?->kondisi_aset ?? '-' }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $row->penanggungJawab?->nama_pegawai ?? '-' }}</td>
                </tr>
            @endforeach
            @for($i = $groupRows->count(); $i < 6; $i++)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor
            </tbody>
        </table>

        <div class="kir-sign">
            <div>
                <div>MENGETAHUI,</div>
                <div>KEPALA PUSAT PELAYANAN KESEHATAN PEGAWAI</div>
                <div>PROVINSI DKI JAKARTA</div>
                <div class="name-space">Nama Kepala Pusat</div>
                <div>NIP. ....................</div>
            </div>
            <div>
                <div>PENGURUS BARANG</div>
                <div>PUSAT PELAYANAN KESEHATAN PEGAWAI</div>
                <div>PROVINSI DKI JAKARTA</div>
                <div class="name-space">Nama Pengurus Barang</div>
                <div>NIP. ....................</div>
            </div>
            <div>
                <div>Jakarta, {{ now()->format('d F Y') }}</div>
                <div>KEPALA RUANGAN/UNIT KERJA</div>
                <div>&nbsp;</div>
                <div class="name-space">Nama Kepala Unit Kerja</div>
                <div>NIP. ....................</div>
            </div>
        </div>
    </div>
@empty
    <div class="kir-paper">
        Belum ada data KIR untuk dicetak.
    </div>
@endforelse

@if(!empty($printMode))
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 120);
    });
</script>
@endif
</body>
</html>

