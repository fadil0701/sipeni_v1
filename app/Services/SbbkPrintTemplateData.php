<?php

namespace App\Services;

use App\Enums\DistribusiStatus;
use App\Models\TransaksiDistribusi;
use Carbon\CarbonInterface;

class SbbkPrintTemplateData
{
    /**
     * @return list<string>
     */
    private static function bulanRomawiList(): array
    {
        return ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    }

    public static function bulanRomawiFromDate(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '-';
        }
        $m = (int) $date->format('n');

        return self::bulanRomawiList()[$m - 1] ?? '-';
    }

    private static function ttdNamaFromConfig(string $configKey, string $blank = '_________________________'): string
    {
        $v = trim((string) config($configKey, ''));

        return $v !== '' ? $v : $blank;
    }

    private static function ttdNipFromConfig(string $configKey, string $blank = 'NIP. _______________________'): string
    {
        $v = trim((string) config($configKey, ''));
        if ($v === '') {
            return $blank;
        }
        $upper = mb_strtoupper($v);

        return str_starts_with($upper, 'NIP') ? $v : 'NIP. '.$v;
    }

    private static function nipBaris(?string $nip): string
    {
        $nip = trim((string) $nip);
        if ($nip === '' || $nip === '-') {
            return 'NIP. _________________________';
        }

        return 'NIP. '.$nip;
    }

    /**
     * Logo kop (data URI) agar cetak / pratinjau tidak bergantung URL publik.
     */
    public static function kopLogoHtml(): string
    {
        $configured = trim((string) config('sipeni.sbbk_kop_logo_path', ''));
        if ($configured === '') {
            return '';
        }
        $path = (str_starts_with($configured, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $configured))
            ? $configured
            : base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configured));
        $real = realpath($path);
        $base = realpath(base_path());
        if ($real === false || $base === false || ! is_readable($real)) {
            return '';
        }
        $realLower = strtolower($real);
        $baseLower = strtolower($base);
        if (! str_starts_with($realLower, $baseLower)) {
            return '';
        }
        $mime = @mime_content_type($real);
        $mime = is_string($mime) && $mime !== '' ? $mime : 'image/png';
        $bin = @file_get_contents($real);
        if ($bin === false || $bin === '') {
            return '';
        }

        return '<img src="data:'.htmlspecialchars($mime, ENT_QUOTES, 'UTF-8').';base64,'.base64_encode($bin).'" alt="" style="max-height:22mm;max-width:26mm;object-fit:contain;" />';
    }

    /**
     * Grup nama variabel untuk UI admin (chip placeholder) — selaras dengan {@see payload()}.
     *
     * @return array{raw: array<int, string>, escaped: array<int, string>}
     */
    public static function variableGroups(): array
    {
        return [
            'raw' => ['detail_rows_html', 'kop_logo_html'],
            'escaped' => [
                'app_name',
                'judul_dokumen',
                'kop_baris1',
                'kop_baris2',
                'kop_baris3',
                'kop_baris4',
                'kop_alamat',
                'kop_kota',
                'no_sbbk',
                'bulan_romawi_distribusi',
                'tahun_distribusi',
                'tanggal_distribusi',
                'tanggal_distribusi_tgl',
                'status_label',
                'permintaan_no',
                'unit_kerja_nama',
                'pemohon_nama',
                'pemohon_nip',
                'gudang_asal_nama',
                'gudang_tujuan_nama',
                'pengirim_nama',
                'pengirim_nip',
                'pengirim_nip_line',
                'penerima_nip_line',
                'admin_gudang_nama',
                'admin_gudang_nip',
                'penerima_nama',
                'penerima_nip',
                'keterangan',
                'total_subtotal',
                'ttd_mengetahui_jabatan',
                'ttd_mengetahui_nama',
                'ttd_mengetahui_nip',
                'ttd_pengurus_barang_nama',
                'ttd_pengurus_barang_nip',
            ],
        ];
    }

    /**
     * Data untuk template cetak key `distribusi.sbbk` (PrintTemplateRenderer).
     */
    public static function payload(TransaksiDistribusi $distribusi): array
    {
        $distribusi->loadMissing([
            'permintaan.unitKerja',
            'permintaan.pemohon',
            'gudangAsal',
            'gudangTujuan',
            'pegawaiPengirim',
            'penerimaanBarang.pegawaiPenerima',
            'detailDistribusi.inventory.dataBarang',
            'detailDistribusi.inventory.sumberAnggaran',
            'detailDistribusi.satuan',
        ]);

        $status = $distribusi->status_distribusi instanceof DistribusiStatus
            ? $distribusi->status_distribusi
            : DistribusiStatus::normalizeStored((string) ($distribusi->getAttributes()['status_distribusi'] ?? ''));

        $statusLabel = match ($status) {
            DistribusiStatus::Draft => 'Draft',
            DistribusiStatus::Diproses => 'Diproses',
            DistribusiStatus::Dikirim => 'Dikirim',
            DistribusiStatus::Selesai => 'Selesai',
        };

        $permintaan = $distribusi->permintaan;
        $tgl = $distribusi->tanggal_distribusi;

        $pemohon = $permintaan?->pemohon;
        $pengirim = $distribusi->pegawaiPengirim;

        $penerimaan = $distribusi->penerimaanBarang->sortByDesc('id_penerimaan')->first();
        $pegPenerima = $penerimaan?->pegawaiPenerima;
        $penerimaNama = $pegPenerima?->nama_pegawai ?? $pemohon?->nama_pegawai ?? '-';
        $penerimaNipRaw = $pegPenerima ?? $pemohon;
        $penerimaNip = ($penerimaNipRaw && trim((string) $penerimaNipRaw->nip_pegawai) !== '')
            ? (string) $penerimaNipRaw->nip_pegawai
            : '_____________________';

        $total = 0.0;
        $rows = '';
        foreach ($distribusi->detailDistribusi as $index => $detail) {
            $inv = $detail->inventory;
            $barang = $inv?->dataBarang;
            $kode = trim((string) ($barang?->kode_data_barang ?? '')) !== '' ? (string) $barang->kode_data_barang : '-';
            $nama = $barang?->nama_barang ?? '-';
            $merk = trim((string) ($inv?->merk ?? ''));
            $tipe = trim((string) ($inv?->tipe ?? '')) !== '' ? (string) $inv->tipe : '';
            $merkType = match (true) {
                $merk !== '' && $tipe !== '' => $merk.' / '.$tipe,
                $merk !== '' => $merk,
                $tipe !== '' => $tipe,
                default => '-',
            };
            $qty = number_format((float) $detail->qty_distribusi, 2, ',', '.');
            $sat = $detail->satuan?->nama_satuan ?? '-';
            $harga = number_format((float) $detail->harga_satuan, 2, ',', '.');
            $sub = (float) $detail->subtotal;
            $total += $sub;
            $thAng = $inv?->tahun_anggaran !== null && $inv->tahun_anggaran !== ''
                ? (string) $inv->tahun_anggaran
                : '-';
            $perolehan = $inv?->sumberAnggaran?->nama_anggaran
                ?? (trim((string) ($inv?->jenis_barang ?? '')) !== '' ? (string) $inv->jenis_barang : '-');
            $rows .= '<tr>'
                .'<td style="text-align:center;padding:4px 3px;border:1px solid #000;font-size:9pt;">'.($index + 1).'</td>'
                .'<td style="padding:4px 3px;border:1px solid #000;font-size:9pt;">'.e($kode).'</td>'
                .'<td style="padding:4px 3px;border:1px solid #000;font-size:9pt;">'.e($nama).'</td>'
                .'<td style="padding:4px 3px;border:1px solid #000;font-size:9pt;">'.e($merkType).'</td>'
                .'<td style="padding:4px 3px;border:1px solid #000;font-size:9pt;">'.e($sat).'</td>'
                .'<td style="text-align:right;padding:4px 3px;border:1px solid #000;font-size:9pt;">'.$qty.'</td>'
                .'<td style="text-align:right;padding:4px 3px;border:1px solid #000;font-size:9pt;">Rp '.$harga.'</td>'
                .'<td style="text-align:center;padding:4px 3px;border:1px solid #000;font-size:9pt;">'.e($thAng).'</td>'
                .'<td style="padding:4px 3px;border:1px solid #000;font-size:9pt;">'.e((string) $perolehan).'</td>'
                .'</tr>';
        }

        $pengirimNipRaw = ($pengirim && trim((string) $pengirim->nip_pegawai) !== '') ? (string) $pengirim->nip_pegawai : '';

        return [
            'app_name' => config('app.name'),
            'judul_dokumen' => 'SURAT BUKTI BARANG KELUAR (SBBK)',
            'kop_logo_html' => self::kopLogoHtml(),
            'kop_baris1' => (string) config('sipeni.sbbk_kop_baris1', ''),
            'kop_baris2' => (string) config('sipeni.sbbk_kop_baris2', ''),
            'kop_baris3' => (string) config('sipeni.sbbk_kop_baris3', ''),
            'kop_baris4' => (string) config('sipeni.sbbk_kop_baris4', ''),
            'kop_alamat' => (string) config('sipeni.sbbk_kop_alamat', ''),
            'kop_kota' => (string) config('sipeni.sbbk_kop_kota', ''),
            'no_sbbk' => $distribusi->no_sbbk ?? '-',
            'bulan_romawi_distribusi' => self::bulanRomawiFromDate($tgl),
            'tahun_distribusi' => $tgl ? $tgl->format('Y') : '-',
            'tanggal_distribusi' => optional($tgl)->format('d/m/Y H:i') ?? '-',
            'tanggal_distribusi_tgl' => optional($tgl)->format('d/m/Y') ?? '-',
            'status_label' => $statusLabel,
            'permintaan_no' => $permintaan?->no_permintaan ?? '-',
            'unit_kerja_nama' => $permintaan?->unitKerja?->nama_unit_kerja ?? '-',
            'pemohon_nama' => $pemohon?->nama_pegawai ?? '-',
            'pemohon_nip' => ($pemohon && trim((string) $pemohon->nip_pegawai) !== '') ? (string) $pemohon->nip_pegawai : '-',
            'gudang_asal_nama' => $distribusi->gudangAsal?->nama_gudang ?? '-',
            'gudang_tujuan_nama' => $distribusi->gudangTujuan?->nama_gudang ?? '-',
            'pengirim_nama' => $pengirim?->nama_pegawai ?? '-',
            'pengirim_nip' => $pengirimNipRaw !== '' ? $pengirimNipRaw : '_____________________',
            'pengirim_nip_line' => self::nipBaris($pengirimNipRaw !== '' ? $pengirimNipRaw : null),
            'penerima_nip_line' => self::nipBaris(
                ($penerimaNipRaw && trim((string) $penerimaNipRaw->nip_pegawai) !== '')
                    ? (string) $penerimaNipRaw->nip_pegawai
                    : null
            ),
            'admin_gudang_nama' => self::ttdNamaFromConfig('sipeni.sbbk_ttd_admin_gudang_nama'),
            'admin_gudang_nip' => self::ttdNipFromConfig('sipeni.sbbk_ttd_admin_gudang_nip'),
            'penerima_nama' => $penerimaNama,
            'penerima_nip' => $penerimaNip,
            'keterangan' => ($distribusi->keterangan !== null && $distribusi->keterangan !== '') ? (string) $distribusi->keterangan : '-',
            'total_subtotal' => 'Rp '.number_format($total, 2, ',', '.'),
            'detail_rows_html' => $rows,
            'ttd_mengetahui_jabatan' => 'Kepala Subbag Tata Usaha',
            'ttd_mengetahui_nama' => self::ttdNamaFromConfig('sipeni.sbbk_ttd_mengetahui_nama'),
            'ttd_mengetahui_nip' => self::ttdNipFromConfig('sipeni.sbbk_ttd_mengetahui_nip'),
            'ttd_pengurus_barang_nama' => self::ttdNamaFromConfig('sipeni.sbbk_ttd_pengurus_barang_nama'),
            'ttd_pengurus_barang_nip' => self::ttdNipFromConfig('sipeni.sbbk_ttd_pengurus_barang_nip'),
        ];
    }
}
