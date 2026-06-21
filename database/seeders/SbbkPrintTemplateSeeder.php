<?php

namespace Database\Seeders;

use App\Models\PrintTemplate;
use App\Services\SbbkPrintTemplateData;
use Illuminate\Database\Seeder;

class SbbkPrintTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $cell = 'padding:4px 3px;border:1px solid #000;font-size:9pt;';
        $sampleDetailRows = <<<HTML
<tr><td style="text-align:center;{$cell}">1</td><td style="{$cell}">KB-001</td><td style="{$cell}">Contoh nama barang</td><td style="{$cell}">Merk A / Tipe A</td><td style="{$cell}">Buah</td><td style="text-align:right;{$cell}">10,00</td><td style="text-align:right;{$cell}">Rp 5.000,00</td><td style="text-align:center;{$cell}">2026</td><td style="{$cell}">APBD</td></tr>
<tr><td style="text-align:center;{$cell}">2</td><td style="{$cell}">KB-002</td><td style="{$cell}">Contoh barang kedua</td><td style="{$cell}">-</td><td style="{$cell}">Pcs</td><td style="text-align:right;{$cell}">2,00</td><td style="text-align:right;{$cell}">Rp 15.000,00</td><td style="text-align:center;{$cell}">2026</td><td style="{$cell}">APBD</td></tr>
HTML;

        $body = <<<'HTML'
<div style="max-width:210mm;margin:0 auto;padding:8mm 10mm;font-family:'Times New Roman',Times,serif;font-size:11pt;color:#000;">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
    <div style="flex:0 0 auto;">{{{kop_logo_html}}}</div>
    <div style="flex:1;text-align:center;line-height:1.35;">
      <div style="font-size:11pt;font-weight:bold;">{{kop_baris1}}</div>
      <div style="font-size:11pt;font-weight:bold;">{{kop_baris2}}</div>
      <div style="font-size:11pt;font-weight:bold;">{{kop_baris3}}</div>
      <div style="font-size:11pt;font-weight:bold;">{{kop_baris4}}</div>
      <div style="font-size:10pt;margin-top:3px;">{{kop_alamat}}</div>
      <div style="font-size:10pt;font-weight:bold;margin-top:2px;">{{kop_kota}}</div>
    </div>
  </div>
  <div style="text-align:center;margin-top:10px;">
    <div style="font-size:12pt;font-weight:bold;">{{judul_dokumen}}</div>
  </div>
  <table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:11pt;">
    <tr>
      <td style="width:20%;padding:2px 0;vertical-align:top;"><strong>Nomor</strong></td>
      <td style="padding:2px 0;">: {{no_sbbk}}</td>
    </tr>
    <tr>
      <td style="padding:2px 0;vertical-align:top;"><strong>Unit Kerja</strong></td>
      <td style="padding:2px 0;">: {{unit_kerja_nama}}</td>
    </tr>
    <tr>
      <td style="padding:2px 0;vertical-align:top;"><strong>Tanggal</strong></td>
      <td style="padding:2px 0;">: {{tanggal_distribusi_tgl}}</td>
    </tr>
  </table>

  <table style="width:100%;border-collapse:collapse;margin-top:12px;">
    <thead>
      <tr>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:4%;vertical-align:middle;">No</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:11%;vertical-align:middle;">Kode<br>Barang</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:22%;vertical-align:middle;">Nama<br>Barang</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:12%;vertical-align:middle;">Merk<br>Type</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:8%;vertical-align:middle;">Satuan</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:8%;vertical-align:middle;">Jumlah</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:11%;vertical-align:middle;">Harga<br>Satuan</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:8%;vertical-align:middle;">Tahun<br>Perolehan</th>
        <th style="border:1px solid #000;padding:4px 2px;font-size:8.5pt;font-weight:bold;width:16%;vertical-align:middle;">Anggaran</th>
      </tr>
    </thead>
    <tbody>
      {{{detail_rows_html}}}
    </tbody>
    <tfoot>
      <tr>
        <td colspan="8" style="text-align:right;padding:6px;border:1px solid #000;font-weight:bold;font-size:9pt;">Jumlah</td>
        <td style="text-align:right;padding:6px;border:1px solid #000;font-weight:bold;font-size:9pt;">{{total_subtotal}}</td>
      </tr>
    </tfoot>
  </table>

  <table style="width:100%;border-collapse:collapse;margin-top:26px;font-size:10pt;">
    <tr>
      <td style="width:33%;text-align:center;vertical-align:top;padding:4px 6px;">
        <strong>Admin Gudang</strong>
        <div style="min-height:56px;"></div>
        <div>{{admin_gudang_nama}}</div>
        <div style="margin-top:2px;">{{admin_gudang_nip}}</div>
      </td>
      <td style="width:34%;text-align:center;vertical-align:top;padding:4px 6px;">
        <strong>Pengirim Barang</strong>
        <div style="min-height:56px;"></div>
        <div>{{pengirim_nama}}</div>
        <div style="margin-top:2px;">{{pengirim_nip_line}}</div>
      </td>
      <td style="width:33%;text-align:center;vertical-align:top;padding:4px 6px;">
        <strong>Penerima Barang</strong>
        <div style="min-height:56px;"></div>
        <div>{{penerima_nama}}</div>
        <div style="margin-top:2px;">{{penerima_nip_line}}</div>
      </td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:center;padding-top:18px;padding-bottom:6px;">
        Mengetahui,<br>
        <strong>{{ttd_mengetahui_jabatan}}</strong>
        <div style="min-height:52px;"></div>
        <div>{{ttd_mengetahui_nama}}</div>
        <div style="margin-top:2px;">{{ttd_mengetahui_nip}}</div>
      </td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:center;padding-top:14px;">
        <strong>Pengurus Barang</strong>
        <div style="min-height:48px;"></div>
        <div>{{ttd_pengurus_barang_nama}}</div>
        <div style="margin-top:2px;">{{ttd_pengurus_barang_nip}}</div>
      </td>
    </tr>
  </table>
</div>
HTML;

        PrintTemplate::query()->updateOrCreate(
            ['key' => 'distribusi.sbbk'],
            [
                'name' => 'SBBK — Surat Bukti Barang Keluar',
                'description' => 'Mengikuti contoh PDF: kop 4 baris + alamat + kota, Nomor/Unit Kerja/Tanggal, tabel 9 kolom (nama & merk/type terpisah), total jumlah, blok tanda tangan. Logo opsional (config sipeni.sbbk_kop_logo_path). Variabel {{{detail_rows_html}}} = baris &lt;tr&gt; dari sistem.',
                'body' => $body,
                'sample_data' => [
                    'app_name' => config('app.name'),
                    'judul_dokumen' => 'SURAT BUKTI BARANG KELUAR (SBBK)',
                    'kop_logo_html' => SbbkPrintTemplateData::kopLogoHtml(),
                    'kop_baris1' => (string) config('sipeni.sbbk_kop_baris1', ''),
                    'kop_baris2' => (string) config('sipeni.sbbk_kop_baris2', ''),
                    'kop_baris3' => (string) config('sipeni.sbbk_kop_baris3', ''),
                    'kop_baris4' => (string) config('sipeni.sbbk_kop_baris4', ''),
                    'kop_alamat' => (string) config('sipeni.sbbk_kop_alamat', ''),
                    'kop_kota' => (string) config('sipeni.sbbk_kop_kota', ''),
                    'no_sbbk' => '001/NA-SBBK/PPKP/V/2026',
                    'bulan_romawi_distribusi' => 'V',
                    'tahun_distribusi' => '2026',
                    'tanggal_distribusi' => '02/05/2026 10:00',
                    'tanggal_distribusi_tgl' => '02/05/2026',
                    'unit_kerja_nama' => 'Unit Kerja Contoh',
                    'pengirim_nama' => 'Nama Pengirim',
                    'pengirim_nip' => '198502022010012002',
                    'pengirim_nip_line' => 'NIP. 198502022010012002',
                    'admin_gudang_nama' => '_________________________',
                    'admin_gudang_nip' => 'NIP. _______________________',
                    'penerima_nama' => 'Nama Penerima',
                    'penerima_nip' => '198503032010013003',
                    'penerima_nip_line' => 'NIP. 198503032010013003',
                    'total_subtotal' => 'Rp 80.000,00',
                    'ttd_mengetahui_jabatan' => 'Kepala Subbag Tata Usaha',
                    'ttd_mengetahui_nama' => '( _________________________ )',
                    'ttd_mengetahui_nip' => 'NIP. _______________________',
                    'ttd_pengurus_barang_nama' => '( _________________________ )',
                    'ttd_pengurus_barang_nip' => 'NIP. _______________________',
                    'detail_rows_html' => trim($sampleDetailRows),
                ],
                'is_active' => true,
            ]
        );
    }
}
