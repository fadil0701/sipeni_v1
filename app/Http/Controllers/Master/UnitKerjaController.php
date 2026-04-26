<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterUnitKerja;

class UnitKerjaController extends Controller
{
    private const DKI_WILAYAH = [
        'KOTA ADMINISTRASI JAKARTA PUSAT' => [
            'GAMBIR', 'SAWAH BESAR', 'KEMAYORAN', 'SENEN', 'CEMPAKA PUTIH',
            'MENTENG', 'TANAH ABANG', 'JOHAR BARU',
        ],
        'KOTA ADMINISTRASI JAKARTA UTARA' => [
            'PENJARINGAN', 'PADEMNGAN', 'TANJUNG PRIOK', 'KOJA', 'KELAPA GADING', 'CILINCING',
        ],
        'KOTA ADMINISTRASI JAKARTA BARAT' => [
            'CENGKARENG', 'GROGOL PETAMBURAN', 'TAMAN SARI', 'TAMBORA', 'KEBON JERUK',
            'KALIDERES', 'PALMERAH', 'KEMBANGAN',
        ],
        'KOTA ADMINISTRASI JAKARTA SELATAN' => [
            'TEBET', 'SETIABUDI', 'MAMPANG PRAPATAN', 'PASAR MINGGU', 'KEBAYORAN LAMA',
            'CILANDAK', 'KEBAYORAN BARU', 'PANCORAN', 'JAGAKARSA', 'PESANGGRAHAN',
        ],
        'KOTA ADMINISTRASI JAKARTA TIMUR' => [
            'MATRAMAN', 'PULOGADUNG', 'JATINEGARA', 'KRAMAT JATI', 'PASAR REBO',
            'CAKUNG', 'DUREN SAWIT', 'MAKASAR', 'CIRACAS', 'CIPAYUNG',
        ],
        'KABUPATEN ADMINISTRASI KEPULUAUAN SERIBU' => [
            'KEPULUAUAN SERIBU UTARA', 'KEPULUAUAN SERIBU SELATAN',
        ],
    ];

    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $unitKerjas = MasterUnitKerja::latest()->paginate($perPage)->appends($request->query());
        return view('master.unit-kerja.index', compact('unitKerjas'));
    }

    public function create()
    {
        $wilayahDki = self::DKI_WILAYAH;
        return view('master.unit-kerja.create', compact('wilayahDki'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_unit_kerja' => 'required|string|max:255|unique:master_unit_kerja,kode_unit_kerja',
            'nama_unit_kerja' => 'required|string|max:255',
            'kota_kabupaten' => 'required|string|in:' . implode(',', array_keys(self::DKI_WILAYAH)),
            'kecamatan' => 'required|string',
        ]);

        $allowedKecamatans = self::DKI_WILAYAH[$validated['kota_kabupaten']] ?? [];
        if (!in_array($validated['kecamatan'], $allowedKecamatans, true)) {
            return back()
                ->withErrors(['kecamatan' => 'Kecamatan tidak valid untuk Kota/Kabupaten yang dipilih.'])
                ->withInput();
        }

        MasterUnitKerja::create($validated);

        return redirect()->route('master.unit-kerja.index')
            ->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    public function show($id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);
        return view('master.unit-kerja.show', compact('unitKerja'));
    }

    public function edit($id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);
        $wilayahDki = self::DKI_WILAYAH;
        return view('master.unit-kerja.edit', compact('unitKerja', 'wilayahDki'));
    }

    public function update(Request $request, $id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);

        $validated = $request->validate([
            'kode_unit_kerja' => 'required|string|max:255|unique:master_unit_kerja,kode_unit_kerja,' . $id . ',id_unit_kerja',
            'nama_unit_kerja' => 'required|string|max:255',
            'kota_kabupaten' => 'required|string|in:' . implode(',', array_keys(self::DKI_WILAYAH)),
            'kecamatan' => 'required|string',
        ]);

        $allowedKecamatans = self::DKI_WILAYAH[$validated['kota_kabupaten']] ?? [];
        if (!in_array($validated['kecamatan'], $allowedKecamatans, true)) {
            return back()
                ->withErrors(['kecamatan' => 'Kecamatan tidak valid untuk Kota/Kabupaten yang dipilih.'])
                ->withInput();
        }

        $unitKerja->update($validated);

        return redirect()->route('master.unit-kerja.index')
            ->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $unitKerja = MasterUnitKerja::findOrFail($id);
        $unitKerja->delete();

        return redirect()->route('master.unit-kerja.index')
            ->with('success', 'Unit Kerja berhasil dihapus.');
    }
}

