<?php

namespace App\Services;

use App\Models\ReturBarang;

class ReturPrintTemplateData
{
    /**
     * @return array{raw: array<int, string>, escaped: array<int, string>}
     */
    public static function variableGroups(): array
    {
        return [
            'raw' => ['detail_rows_html'],
            'escaped' => [
                'app_name',
                'judul_dokumen',
                'no_retur',
                'tanggal_retur',
                'status_retur',
                'unit_kerja_nama',
                'gudang_asal_nama',
                'gudang_tujuan_nama',
                'pengirim_nama',
                'alasan_retur',
                'keterangan',
            ],
        ];
    }

    public static function payload(ReturBarang $retur): array
    {
        $retur->loadMissing([
            'unitKerja',
            'gudangAsal',
            'gudangTujuan',
            'pegawaiPengirim',
            'detailRetur.inventory.dataBarang',
            'detailRetur.satuan',
        ]);

        $rows = '';
        foreach ($retur->detailRetur as $index => $detail) {
            $nama = $detail->inventory?->dataBarang?->nama_barang ?? '-';
            $qty = number_format((float) $detail->qty_retur, 2, ',', '.');
            $sat = $detail->satuan?->nama_satuan ?? '-';
            $rows .= '<tr>'
                .'<td style="text-align:center;padding:4px;border:1px solid #000;">'.($index + 1).'</td>'
                .'<td style="padding:4px;border:1px solid #000;">'.e($nama).'</td>'
                .'<td style="text-align:right;padding:4px;border:1px solid #000;">'.e($qty).'</td>'
                .'<td style="padding:4px;border:1px solid #000;">'.e($sat).'</td>'
                .'</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="4" style="padding:8px;border:1px solid #000;text-align:center;">Tidak ada detail</td></tr>';
        }

        return [
            'app_name' => (string) config('app.name', 'SI-MANTIK'),
            'judul_dokumen' => 'DOKUMEN PENGEMBALIAN BARANG',
            'no_retur' => (string) $retur->no_retur,
            'tanggal_retur' => optional($retur->tanggal_retur)->format('d-m-Y') ?? '-',
            'status_retur' => (string) $retur->status_retur,
            'unit_kerja_nama' => (string) ($retur->unitKerja?->nama_unit_kerja ?? '-'),
            'gudang_asal_nama' => (string) ($retur->gudangAsal?->nama_gudang ?? '-'),
            'gudang_tujuan_nama' => (string) ($retur->gudangTujuan?->nama_gudang ?? '-'),
            'pengirim_nama' => (string) ($retur->pegawaiPengirim?->nama_pegawai ?? '-'),
            'alasan_retur' => (string) ($retur->alasan_retur ?? '-'),
            'keterangan' => (string) ($retur->keterangan ?? '-'),
            'detail_rows_html' => $rows,
        ];
    }
}
