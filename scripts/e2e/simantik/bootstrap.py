"""Bootstrap data dari Laravel via artisan tinker (opsional)."""

from __future__ import annotations

import json
import os
import subprocess
from pathlib import Path
from typing import Any


PHP_BOOTSTRAP = r"""
use Illuminate\Support\Str;
$jabatan = fn($name) => \App\Models\MasterJabatan::where('nama_jabatan', $name)->first();
$emailFor = function ($name) use ($jabatan) {
    $j = $jabatan($name);
    if (!$j) return null;
    $slug = Str::slug($j->nama_jabatan ?: ('jabatan-'.$j->id_jabatan)) ?: ('jabatan-'.$j->id_jabatan);
    return $slug.'.'.$j->id_jabatan.'@sipeni.local';
};
$unit = \App\Models\MasterUnitKerja::orderBy('id_unit_kerja')->first();
$pegawai = \App\Models\MasterPegawai::orderBy('id')->first();
$barang = \App\Models\MasterDataBarang::orderBy('id_data_barang')->first();
$barangDummy = \App\Models\MasterDataBarang::where('kode_data_barang', 'BRG-DMY-001')->first();
$satuan = \App\Models\MasterSatuan::orderBy('id_satuan')->first();
$gudangPusat = \App\Models\MasterGudang::where('jenis_gudang', 'PUSAT')->where('kategori_gudang', 'PERSEDIAAN')->orderBy('id_gudang')->first();
$gudangPusatIds = \App\Models\MasterGudang::where('jenis_gudang', 'PUSAT')->where('kategori_gudang', 'PERSEDIAAN')->pluck('id_gudang');
$pemohonEmail = getenv('SIMANTIK_EMAIL_PEMOHON') ?: getenv('SIPENI_DEMO_EMAIL_PEMOHON') ?: 'staf-adm.gudang-unit@sipeni.local';
$pemohonUser = \App\Models\User::where('email', $pemohonEmail)->first();
$pemohonPegawai = $pemohonUser
    ? \App\Models\MasterPegawai::where('user_id', $pemohonUser->id)->first()
    : null;
$pemohonUnitId = optional($pemohonPegawai)->id_unit_kerja ?? optional($unit)->id_unit_kerja;
$gudangUnit = \App\Models\MasterGudang::where('jenis_gudang', 'UNIT')
    ->where('kategori_gudang', 'PERSEDIAAN')
    ->when($pemohonUnitId, fn ($q) => $q->where('id_unit_kerja', $pemohonUnitId))
    ->orderBy('id_gudang')
    ->first();
if (! $gudangUnit && $pemohonUnitId) {
    $gudangUnit = \App\Models\MasterGudang::create([
        'id_unit_kerja' => $pemohonUnitId,
        'nama_gudang' => 'Gudang Unit E2E',
        'jenis_gudang' => 'UNIT',
        'kategori_gudang' => 'PERSEDIAAN',
    ]);
}
$inventory = \App\Models\DataInventory::where('auto_qr_code', 'INV-DMY-001')->first()
    ?? \App\Models\DataInventory::where('jenis_inventory', 'PERSEDIAAN')->where('status_inventory', 'AKTIF')->orderBy('id_inventory')->first();
$barangId = optional($barangDummy ?? $barang)->id_data_barang;
$gudangPusatForFlow = optional($inventory)->id_gudang ?? optional($gudangPusat)->id_gudang;
// Bersihkan sisa run otomasi Python agar nomor PMT/SBBK/TERIMA tidak bentrok
$oldPermintaanIds = \App\Models\PermintaanBarang::query()
    ->where('keterangan', 'like', 'Otomasi Python%')
    ->pluck('id_permintaan');
$oldDistribusiIds = \App\Models\TransaksiDistribusi::query()
    ->where(function ($q) use ($oldPermintaanIds) {
        if ($oldPermintaanIds->isNotEmpty()) {
            $q->whereIn('id_permintaan', $oldPermintaanIds);
        }
        $q->orWhere('keterangan', 'like', 'Distribusi otomasi Python%');
    })
    ->pluck('id_distribusi');
if ($oldDistribusiIds->isNotEmpty()) {
    $oldPenerimaanIds = \App\Models\PenerimaanBarang::whereIn('id_distribusi', $oldDistribusiIds)->pluck('id_penerimaan');
    if ($oldPenerimaanIds->isNotEmpty()) {
        \App\Models\DetailPenerimaanBarang::whereIn('id_penerimaan', $oldPenerimaanIds)->delete();
        \App\Models\PenerimaanBarang::whereIn('id_penerimaan', $oldPenerimaanIds)->delete();
    }
    \App\Models\DetailDistribusi::whereIn('id_distribusi', $oldDistribusiIds)->delete();
    \App\Models\TransaksiDistribusi::whereIn('id_distribusi', $oldDistribusiIds)->delete();
}
if ($oldPermintaanIds->isNotEmpty()) {
    \App\Models\ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
        ->whereIn('id_referensi', $oldPermintaanIds)->delete();
    \App\Models\DetailPermintaanBarang::whereIn('id_permintaan', $oldPermintaanIds)->delete();
    \App\Models\PermintaanBarang::whereIn('id_permintaan', $oldPermintaanIds)->delete();
}
// Reset stok dummy setelah cleanup agar flow tidak gagal validasi qty
$stockGudangIds = $gudangPusatIds->merge(collect([$gudangPusatForFlow])->filter())->unique()->values();
if ($barangId && $stockGudangIds->isNotEmpty() && $satuan) {
    foreach ($stockGudangIds as $idGudangPusat) {
        \DB::table('data_stock')->updateOrInsert(
            ['id_data_barang' => $barangId, 'id_gudang' => $idGudangPusat],
            [
                'qty_awal' => 100,
                'qty_masuk' => 0,
                'qty_keluar' => 0,
                'qty_akhir' => 100,
                'id_satuan' => $satuan->id_satuan,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
if ($inventory) {
    \DB::table('data_inventory')->where('id_inventory', $inventory->id_inventory)->update([
        'qty_input' => 100,
        'updated_at' => now(),
    ]);
}
$verifyPerm = \Spatie\Permission\Models\Permission::firstOrCreate(
    ['name' => 'transaction.penerimaan-barang.verify', 'guard_name' => 'web']
);
foreach (['admin_unit'] as $roleName) {
    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
    if ($role && ! $role->hasPermissionTo($verifyPerm)) {
        $role->givePermissionTo($verifyPerm);
    }
}
echo json_encode([
    'jabatan_ids' => [
        'Admin Unit' => optional($jabatan('Admin Unit'))->id_jabatan,
        'Kepala Unit' => optional($jabatan('Kepala Unit'))->id_jabatan,
        'Kasubbag TU' => optional($jabatan('Kasubbag TU'))->id_jabatan,
        'Kepala Pusat' => optional($jabatan('Kepala Pusat'))->id_jabatan,
        'Admin Gudang Persediaan' => optional($jabatan('Admin Gudang Persediaan'))->id_jabatan,
        'Admin Gudang Aset' => optional($jabatan('Admin Gudang Aset'))->id_jabatan,
        'Admin Gudang Farmasi' => optional($jabatan('Admin Gudang Farmasi'))->id_jabatan,
        'Pengadaan Barang' => optional($jabatan('Pengadaan Barang'))->id_jabatan,
        'Keuangan/Bendahara' => optional($jabatan('Keuangan/Bendahara'))->id_jabatan,
    ],
    'emails_by_jabatan' => [
        'Kepala Unit' => $emailFor('Kepala Unit'),
        'Kasubbag TU' => $emailFor('Kasubbag TU'),
        'Kepala Pusat' => $emailFor('Kepala Pusat'),
        'Admin Gudang Persediaan' => $emailFor('Admin Gudang Persediaan'),
        'Admin Gudang Aset' => $emailFor('Admin Gudang Aset'),
        'Admin Gudang Farmasi' => $emailFor('Admin Gudang Farmasi'),
        'Pengadaan Barang' => $emailFor('Pengadaan Barang'),
        'Keuangan/Bendahara' => $emailFor('Keuangan/Bendahara'),
    ],
    'unit_kerja_id' => optional($pemohonPegawai)->id_unit_kerja ?? optional($unit)->id_unit_kerja,
    'pegawai_id' => optional($pemohonPegawai)->id ?? optional($pegawai)->id,
    'barang_id' => optional($barangDummy ?? $barang)->id_data_barang,
    'satuan_id' => optional($satuan)->id_satuan,
    'gudang_pusat_id' => $gudangPusatForFlow,
    'gudang_unit_id' => optional($gudangUnit)->id_gudang,
    'inventory_id' => optional($inventory)->id_inventory,
], JSON_UNESCAPED_UNICODE);
"""


def load_bootstrap(project_root: Path) -> dict[str, Any]:
    """Jalankan `php artisan tinker` untuk ambil ID master data."""
    artisan = project_root / "artisan"
    if not artisan.is_file():
        return {}

    cmd = ["php", str(artisan), "tinker", "--execute", PHP_BOOTSTRAP]
    try:
        proc = subprocess.run(
            cmd,
            cwd=str(project_root),
            capture_output=True,
            text=True,
            timeout=120,
            check=False,
            env=os.environ.copy(),
        )
    except (OSError, subprocess.TimeoutExpired):
        return {}

    output = (proc.stdout or "").strip()
    if not output:
        return {}

    # Ambil baris JSON terakhir yang valid
    for line in reversed(output.splitlines()):
        line = line.strip()
        if line.startswith("{") and line.endswith("}"):
            try:
                return json.loads(line)
            except json.JSONDecodeError:
                continue
    return {}
