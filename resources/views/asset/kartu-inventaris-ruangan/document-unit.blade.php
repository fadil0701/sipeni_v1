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
            margin-bottom: 4px;
        }
        .kir-header .center {
            text-align: center;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .kir-header .meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }
        .kir-header .line { margin-bottom: 2px; }
        .kir-header .left { max-width: 70%; }
        .kir-header .right { text-align: left; }
        .kir-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 6px;
        }
        .kir-table th,
        .kir-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: top;
            white-space: normal;
            word-break: normal;
            overflow-wrap: anywhere;
            line-height: 1.18;
        }
        .kir-table th {
            text-align: center;
            font-weight: 700;
            font-size: 8.4px;
        }
        .kir-table td {
            font-size: 8.2px;
            min-height: 18px;
        }
        .kir-table tbody tr {
            height: 18px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .kir-sign {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            text-align: center;
            font-size: 9px;
        }
        .kir-sign > div {
            min-height: 90px;
        }
        .kir-sign .name-space {
            margin-top: 30px;
            text-decoration: underline;
            min-height: 12px;
        }
        .kir-sign .nip {
            margin-top: 2px;
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
                font-size: 7.8px;
                padding: 1.6px 2px;
            }
            .kir-sign {
                margin-top: 12px;
                font-size: 8px;
            }
            .kir-sign .name-space {
                margin-top: 26px;
            }
            .tte-seal {
                margin: 8px 0 10px;
                padding: 8px 10px;
                border: 1px solid #94a3b8;
                border-radius: 6px;
                font-size: 8px;
                display: flex;
                align-items: flex-start;
                gap: 10px;
                background: #f8fafc;
            }
            .tte-seal .tte-qr { flex-shrink: 0; }
            .tte-seal .tte-qr svg { display: block; }
            .tte-seal .tte-meta { line-height: 1.35; }
            .tte-seal .tte-hash { font-family: ui-monospace, monospace; font-size: 7px; word-break: break-all; }
            .kir-tta-line {
                margin-top: 6px;
                font-size: 7px;
                color: #374151;
                line-height: 1.3;
            }
            .kir-tta-line.muted { color: #9ca3af; font-style: italic; }
        }
    </style>
</head>
<body>
@php
    $rowsByRuangan = $rows->groupBy(function ($row) {
        return $row->ruangan?->id_ruangan ?? 0;
    });
    $kepalaPusat = $signatories['kepala_pusat'] ?? null;
    $pengurusBarang = $signatories['pengurus_barang'] ?? null;
    $kepalaUnit = $signatories['kepala_unit'] ?? null;
    $tteSignaturesByRole = $tteSignaturesByRole ?? collect();
    $tteCanSignRoles = $tteCanSignRoles ?? [];
    $kirSignerLabels = [
        'kepala_pusat' => 'Kepala Pusat (Mengetahui)',
        'pengurus_barang' => 'Pengurus Barang',
        'kepala_unit' => 'Kepala Ruangan/Unit Kerja',
    ];
@endphp

<div class="toolbar print-hidden">
    <a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="btn">Kembali ke Daftar Dokumen KIR</a>
    <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen</button>
    @if(session('success'))
        <span style="margin-left:8px;font-size:12px;color:#047857;">{{ session('success') }}</span>
    @endif
    @if($errors->any())
        <span style="margin-left:8px;font-size:12px;color:#b91c1c;">{{ $errors->first() }}</span>
    @endif
    @if(!empty($tteSeal) && !empty($tteCanSignRoles))
        @foreach($tteCanSignRoles as $role)
            <form method="post" action="{{ route('asset.kartu-inventaris-ruangan.dokumen-sign', $unitKerja->id_unit_kerja) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="public_token" value="{{ $tteSeal->public_token }}">
                <input type="hidden" name="signer_role" value="{{ $role }}">
                <button type="submit" class="btn btn-primary">Tandatangani — {{ $kirSignerLabels[$role] ?? $role }}</button>
            </form>
        @endforeach
    @endif
</div>

@forelse($rowsByRuangan as $groupRows)
    @php
        $first = $groupRows->first();
        $ruangan = $first?->ruangan;
        $tahun = now()->format('Y');
    @endphp
    <div class="kir-paper">
    
        <div class="kir-header">
            <div class="center">
                <div>KARTU INVENTARIS RUANGAN</div>
                <div>TAHUN {{ $tahun }}</div>
            </div>
            <div class="meta">
                <div class="left">
                    <div class="line"><strong>PROVINSI</strong> : PEMERINTAH PROVINSI DKI JAKARTA</div>
                    <div class="line"><strong>KABUPATEN/KOTA</strong> : {{ $unitKerja->kota_kabupaten ?? '-' }}</div>
                    <div class="line"><strong>UNIT KERJA</strong> : {{ $unitKerja->nama_unit_kerja }}</div>
                    <div class="line"><strong>SATUAN KERJA</strong> : PUSAT PELAYANAN KESEHATAN PEGAWAI PROV. DKI JAKARTA</div>
                </div>
                <div class="right">
                    <div class="line" ><strong>KODE UNIT KERJA</strong> : {{ $unitKerja->kode_unit_kerja ?? $unitKerja->id_unit_kerja }}</div>
                    <div class="line"><strong>RUANGAN</strong> : {{ $ruangan?->nama_ruangan ?? '-' }}</div>
                </div>
            </div>
        </div>

        @if($loop->first && !empty($tteSeal) && !empty($tteVerificationUrl))
            <div class="tte-seal">
                <div class="tte-qr" aria-hidden="true">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(96)->generate($tteVerificationUrl) !!}
                </div>
                <div class="tte-meta">
                    <div><strong>Verifikasi dokumen (internal)</strong></div>
                    <div>Kode: <strong>{{ $tteSeal->verification_code }}</strong></div>
                    <div class="tte-hash">SHA-256: {{ $tteSeal->content_hash_sha256 }}</div>
                    <div style="margin-top:4px;">Pindai QR atau buka: {{ $tteVerificationUrl }}</div>
                </div>
            </div>
        @endif

        <table class="kir-table">
            <colgroup>
                <col style="width: 2.8%;">
                <col style="width: 6.8%;">
                <col style="width: 8.8%;">
                <col style="width: 10.8%;">
                <col style="width: 8.6%;">
                <col style="width: 5.2%;">
                <col style="width: 5.2%;">
                <col style="width: 3.7%;">
                <col style="width: 3.7%;">
                <col style="width: 3.4%;">
                <col style="width: 2.8%;">
                <col style="width: 4.8%;">
                <col style="width: 4.1%;">
                <col style="width: 4.2%;">
                <col style="width: 4.2%;">
                <col style="width: 10.2%;">
            </colgroup>
            <thead>
            <tr>
                <th>NO</th>
                <th>KODE DATA BARANG</th>
                <th>KODE REGISTER</th>
                <th>NAMA BARANG</th>
                <th>MERK / MODEL</th>
                <th>JENIS BARANG</th>
                <th>NO.SERI</th>
                <th>TAHUN PEMBUATAN</th>
                <th>TAHUN PEMBELIAN</th>
                <th>JUMLAH BARANG</th>
                <th>SATUAN</th>
                <th>HARGA SATUAN</th>
                <th>KEADAAN BARANG</th>
                <th>TANGGAL PEMELIHARAAN</th>
                <th>TANGGAL KALIBRASI</th>
                <th>KETERANGAN / MUTASI / DLL</th>
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
                <div class="name-space">{{ $kepalaPusat?->nama_pegawai ?? '(Belum diatur)' }}</div>
                <div class="nip">NIP. {{ $kepalaPusat?->nip_pegawai ?? '-' }}</div>
                @if(!empty($tteSeal))
                    @php $sigKp = $tteSignaturesByRole->get('kepala_pusat'); @endphp
                    @if($sigKp && $sigKp->signed_at)
                        <div class="kir-tta-line">TTE (internal): {{ $sigKp->signed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</div>
                    @else
                        <div class="kir-tta-line muted">Menunggu tanda tangan elektronik</div>
                    @endif
                @endif
            </div>
            <div>
                <div>PENGURUS BARANG</div>
                <div>PUSAT PELAYANAN KESEHATAN PEGAWAI</div>
                <div>PROVINSI DKI JAKARTA</div>
                <div class="name-space">{{ $pengurusBarang?->nama_pegawai ?? '(Belum diatur)' }}</div>
                <div class="nip">NIP. {{ $pengurusBarang?->nip_pegawai ?? '-' }}</div>
                @if(!empty($tteSeal))
                    @php $sigPb = $tteSignaturesByRole->get('pengurus_barang'); @endphp
                    @if($sigPb && $sigPb->signed_at)
                        <div class="kir-tta-line">TTE (internal): {{ $sigPb->signed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</div>
                    @else
                        <div class="kir-tta-line muted">Menunggu tanda tangan elektronik</div>
                    @endif
                @endif
            </div>
            <div>
                <div>Jakarta, {{ now()->format('d F Y') }}</div>
                <div>KEPALA RUANGAN/UNIT KERJA</div>
                <div>&nbsp;</div>
                <div class="name-space">{{ $kepalaUnit?->nama_pegawai ?? '(Belum diatur)' }}</div>
                <div class="nip">NIP. {{ $kepalaUnit?->nip_pegawai ?? '-' }}</div>
                @if(!empty($tteSeal))
                    @php $sigKu = $tteSignaturesByRole->get('kepala_unit'); @endphp
                    @if($sigKu && $sigKu->signed_at)
                        <div class="kir-tta-line">TTE (internal): {{ $sigKu->signed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</div>
                    @else
                        <div class="kir-tta-line muted">Menunggu tanda tangan elektronik</div>
                    @endif
                @endif
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

