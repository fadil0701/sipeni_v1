<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
