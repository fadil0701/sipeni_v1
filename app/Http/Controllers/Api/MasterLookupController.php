<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataInventory;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\MasterRuangan;
use Illuminate\Http\JsonResponse;

/**
 * Lookup master data (ruangan, pegawai) yang ter-scope ke unit kerja.
 */
class MasterLookupController extends Controller
{
    /**
     * Gudang pada master_gudang untuk satu unit kerja.
     */
    public function gudangByUnit(int $id_unit_kerja): JsonResponse
    {
        $rows = MasterGudang::query()
            ->where('id_unit_kerja', $id_unit_kerja)
            ->orderBy('nama_gudang')
            ->get();

        return response()->json([
            'data' => $rows->map(static function ($g) {
                return [
                    'id_gudang' => (int) $g->id_gudang,
                    'id_unit_kerja' => (int) $g->id_unit_kerja,
                    'jenis_gudang' => (string) $g->jenis_gudang,
                    'label' => $g->nama_gudang.' ('.$g->jenis_gudang.')',
                ];
            })->values(),
        ]);
    }

    /**
     * Ruangan pada master_ruangan untuk satu unit kerja.
     */
    public function ruanganByUnit(int $id_unit_kerja): JsonResponse
    {
        $rows = MasterRuangan::query()
            ->with('unitKerja')
            ->where('id_unit_kerja', $id_unit_kerja)
            ->orderBy('nama_ruangan')
            ->get();

        return response()->json([
            'data' => $rows->map(static function ($r) {
                return [
                    'id_ruangan' => (int) $r->id_ruangan,
                    'id_unit_kerja' => (int) $r->id_unit_kerja,
                    'label' => $r->nama_ruangan.' ('.($r->unitKerja->nama_unit_kerja ?? '-').')',
                ];
            })->values(),
        ]);
    }

    /**
     * Pegawai pada master_pegawai untuk satu unit kerja.
     */
    public function pegawaiByUnit(int $id_unit_kerja): JsonResponse
    {
        $rows = MasterPegawai::query()
            ->with(['unitKerja', 'jabatan'])
            ->where('id_unit_kerja', $id_unit_kerja)
            ->orderBy('nama_pegawai')
            ->get();

        return response()->json([
            'data' => $rows->map(static function ($p) {
                $jabatan = $p->jabatan->nama_jabatan ?? '';

                return [
                    'id' => (int) $p->id,
                    'id_unit_kerja' => (int) $p->id_unit_kerja,
                    'label' => $jabatan !== ''
                        ? $p->nama_pegawai.' — '.$jabatan
                        : $p->nama_pegawai.' ('.($p->unitKerja->nama_unit_kerja ?? '-').')',
                ];
            })->values(),
        ]);
    }

    /**
     * Inventory aktif pada gudang UNIT untuk satu unit kerja.
     */
    public function inventoryByUnit(int $id_unit_kerja): JsonResponse
    {
        $rows = DataInventory::query()
            ->with(['dataBarang', 'satuan', 'gudang'])
            ->whereHas('gudang', static function ($q) use ($id_unit_kerja) {
                $q->where('id_unit_kerja', $id_unit_kerja)
                    ->where('jenis_gudang', 'UNIT');
            })
            ->where('qty_input', '>', 0)
            ->orderBy('id_inventory')
            ->get();

        return response()->json([
            'data' => $rows->map(static function ($inv) {
                return [
                    'id_inventory' => (int) $inv->id_inventory,
                    'id_data_barang' => (int) $inv->id_data_barang,
                    'nama_barang' => (string) ($inv->dataBarang->nama_barang ?? '-'),
                    'qty_tersedia' => (float) $inv->qty_input,
                    'id_satuan' => (int) ($inv->id_satuan ?? 0),
                    'nama_satuan' => (string) ($inv->satuan->nama_satuan ?? '-'),
                    'id_gudang' => (int) $inv->id_gudang,
                    'nama_gudang' => (string) ($inv->gudang->nama_gudang ?? '-'),
                    'label' => ($inv->dataBarang->nama_barang ?? '-') . ' | Stok: ' . (float) $inv->qty_input . ' ' . ($inv->satuan->nama_satuan ?? ''),
                ];
            })->values(),
        ]);
    }
}
