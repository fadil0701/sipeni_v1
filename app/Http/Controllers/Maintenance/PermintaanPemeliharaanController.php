<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PermintaanPemeliharaanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PermintaanPemeliharaan::with(['registerAset.inventory.dataBarang', 'unitKerja', 'pemohon']);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->where('id_unit_kerja', $pegawai->id_unit_kerja);
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $query->whereRaw('1 = 0');
                $unitKerjas = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
        }

        // Filters
        if ($request->filled('unit_kerja')) {
            $query->where('id_unit_kerja', $request->unit_kerja);
        }

        if ($request->filled('status')) {
            $query->where('status_permintaan', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pemeliharaan', $request->jenis);
        }

        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_permintaan_pemeliharaan', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', function($q) use ($search) {
                      $q->where('nama_pegawai', 'like', "%{$search}%");
                  })
                  ->orWhereHas('registerAset', function($q) use ($search) {
                      $q->where('nomor_register', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $permintaans = $query->latest('tanggal_permintaan')->paginate($perPage)->appends($request->query());

        return view('maintenance.permintaan-pemeliharaan.index', compact('permintaans', 'unitKerjas'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // Filter unit kerja dan pegawai berdasarkan unit kerja user yang login
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                $registerAsets = RegisterAset::where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->where('status_aset', 'AKTIF')
                    ->whereNotNull('id_inventory')
                    ->whereHas('inventory', function ($q) {
                        $q->where('status_inventory', 'AKTIF');
                    })
                    ->with(['inventory.dataBarang'])
                    ->get();
            } else {
                $unitKerjas = collect([]);
                $pegawais = collect([]);
                $registerAsets = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
            $pegawais = MasterPegawai::all();
            $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
                ->whereNotNull('id_inventory')
                ->whereHas('inventory', function ($q) {
                    $q->where('status_inventory', 'AKTIF');
                })
                ->with(['inventory.dataBarang'])
                ->get();
        }

        return view('maintenance.permintaan-pemeliharaan.create', compact('unitKerjas', 'pegawais', 'registerAsets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_pemohon' => 'required|exists:master_pegawai,id',
            'tanggal_permintaan' => 'required|date',
            'keterangan' => 'nullable|string',
            'status_permintaan' => 'nullable|in:DRAFT,DIAJUKAN',
            'rows' => 'required|array|min:1',
            'rows.*.id_register_aset' => 'required|distinct|exists:register_aset,id_register_aset',
            'rows.*.jenis_pemeliharaan' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'rows.*.prioritas' => 'required|in:RENDAH,SEDANG,TINGGI,DARURAT',
            'rows.*.deskripsi_kerusakan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pemohon = MasterPegawai::findOrFail($validated['id_pemohon']);

            if ((int) $pemohon->id_unit_kerja !== (int) $validated['id_unit_kerja']) {
                throw new \RuntimeException('Pemohon harus berasal dari unit kerja yang sama.');
            }

            // Generate nomor permintaan berurutan secara aman untuk request paralel.
            $tahun = date('Y');
            $urutan = $this->nextUrutanPermintaan($tahun);
            $statusPermintaan = $validated['status_permintaan'] ?? 'DRAFT';

            foreach ($validated['rows'] as $row) {
                $register = RegisterAset::with('kartuInventarisRuangan')->findOrFail($row['id_register_aset']);

                if ((int) $register->id_unit_kerja !== (int) $validated['id_unit_kerja']) {
                    throw new \RuntimeException('Unit kerja register aset tidak sesuai dengan unit kerja permintaan.');
                }
                if ($register->kartuInventarisRuangan()->count() === 0) {
                    throw new \RuntimeException('Aset belum ditempatkan di KIR, silakan lengkapi penempatan terlebih dahulu.');
                }

                $noPermintaan = 'PMH/' . $tahun . '/' . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
                $urutan++;

                $permintaan = PermintaanPemeliharaan::create([
                    'no_permintaan_pemeliharaan' => $noPermintaan,
                    'id_register_aset' => $row['id_register_aset'],
                    'id_unit_kerja' => $validated['id_unit_kerja'],
                    'id_pemohon' => $validated['id_pemohon'],
                    'tanggal_permintaan' => $validated['tanggal_permintaan'],
                    'jenis_pemeliharaan' => $row['jenis_pemeliharaan'],
                    'prioritas' => $row['prioritas'],
                    'status_permintaan' => $statusPermintaan,
                    'deskripsi_kerusakan' => $row['deskripsi_kerusakan'] ?? null,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                if ($statusPermintaan === 'DIAJUKAN') {
                    $this->createApprovalLogs($permintaan->id_permintaan_pemeliharaan);
                }
            }

            DB::commit();
            return redirect()->route('maintenance.permintaan-pemeliharaan.index')
                ->with('success', 'Permintaan pemeliharaan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat permintaan pemeliharaan: ' . $e->getMessage());
        }
    }

    private function nextUrutanPermintaan(string $tahun): int
    {
        $prefix = 'PMH/' . $tahun . '/';

        $lastNoPermintaan = PermintaanPemeliharaan::query()
            ->where('no_permintaan_pemeliharaan', 'like', $prefix . '%')
            ->orderByDesc('id_permintaan_pemeliharaan')
            ->lockForUpdate()
            ->value('no_permintaan_pemeliharaan');

        $next = 1;
        if ($lastNoPermintaan && preg_match('/(\d{4})$/', $lastNoPermintaan, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        while (PermintaanPemeliharaan::query()
            ->where('no_permintaan_pemeliharaan', $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT))
            ->exists()) {
            $next++;
        }

        return $next;
    }

    public function show($id)
    {
        $permintaan = PermintaanPemeliharaan::with([
            'registerAset.inventory.dataBarang',
            'unitKerja',
            'pemohon',
            'serviceReport',
            'kalibrasi',
            'approvalLogs.approvalFlow.role',
            'approvalLogs.user',
        ])->findOrFail($id);

        $approvalLogs = ApprovalLog::getLogsForReference('PERMINTAAN_PEMELIHARAAN', $id);

        return view('maintenance.permintaan-pemeliharaan.show', compact('permintaan', 'approvalLogs'));
    }

    public function edit($id)
    {
        $permintaan = PermintaanPemeliharaan::with(['registerAset', 'unitKerja', 'pemohon'])->findOrFail($id);
        
        // Hanya bisa edit jika status DRAFT
        if ($permintaan->status_permintaan !== 'DRAFT') {
            return redirect()->route('maintenance.permintaan-pemeliharaan.show', $id)
                ->with('error', 'Permintaan yang sudah diajukan tidak dapat diedit.');
        }

        $user = Auth::user();
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                $registerAsets = RegisterAset::where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->where('status_aset', 'AKTIF')
                    ->whereNotNull('id_inventory')
                    ->whereHas('inventory', function ($q) {
                        $q->where('status_inventory', 'AKTIF');
                    })
                    ->with(['inventory.dataBarang'])
                    ->get();
            } else {
                $unitKerjas = collect([]);
                $pegawais = collect([]);
                $registerAsets = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
            $pegawais = MasterPegawai::all();
            $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
                ->whereNotNull('id_inventory')
                ->whereHas('inventory', function ($q) {
                    $q->where('status_inventory', 'AKTIF');
                })
                ->with(['inventory.dataBarang'])
                ->get();
        }

        return view('maintenance.permintaan-pemeliharaan.edit', compact('permintaan', 'unitKerjas', 'pegawais', 'registerAsets'));
    }

    public function update(Request $request, $id)
    {
        $permintaan = PermintaanPemeliharaan::findOrFail($id);
        
        // Hanya bisa edit jika status DRAFT
        if ($permintaan->status_permintaan !== 'DRAFT') {
            return redirect()->route('maintenance.permintaan-pemeliharaan.show', $id)
                ->with('error', 'Permintaan yang sudah diajukan tidak dapat diedit.');
        }

        $request->validate([
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_pemohon' => 'required|exists:master_pegawai,id',
            'tanggal_permintaan' => 'required|date',
            'jenis_pemeliharaan' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'prioritas' => 'required|in:RENDAH,SEDANG,TINGGI,DARURAT',
            'deskripsi_kerusakan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $register = RegisterAset::with('kartuInventarisRuangan')->findOrFail($request->id_register_aset);
            $pemohon = MasterPegawai::findOrFail($request->id_pemohon);

            if ((int) $register->id_unit_kerja !== (int) $request->id_unit_kerja) {
                throw new \RuntimeException('Unit kerja register aset tidak sesuai dengan unit kerja permintaan.');
            }
            if ((int) $pemohon->id_unit_kerja !== (int) $request->id_unit_kerja) {
                throw new \RuntimeException('Pemohon harus berasal dari unit kerja yang sama.');
            }
            if ($register->kartuInventarisRuangan()->count() === 0) {
                throw new \RuntimeException('Aset belum ditempatkan di KIR, silakan lengkapi penempatan terlebih dahulu.');
            }

            $oldStatus = $permintaan->status_permintaan;
            
            $permintaan->update([
                'id_register_aset' => $request->id_register_aset,
                'id_unit_kerja' => $request->id_unit_kerja,
                'id_pemohon' => $request->id_pemohon,
                'tanggal_permintaan' => $request->tanggal_permintaan,
                'jenis_pemeliharaan' => $request->jenis_pemeliharaan,
                'prioritas' => $request->prioritas,
                'status_permintaan' => $request->status_permintaan ?? 'DRAFT',
                'deskripsi_kerusakan' => $request->deskripsi_kerusakan,
                'keterangan' => $request->keterangan,
            ]);

            // Jika status berubah dari DRAFT ke DIAJUKAN, buat approval logs
            if ($oldStatus === 'DRAFT' && $request->status_permintaan === 'DIAJUKAN') {
                $this->createApprovalLogs($permintaan->id_permintaan_pemeliharaan);
            }

            DB::commit();
            return redirect()->route('maintenance.permintaan-pemeliharaan.index')
                ->with('success', 'Permintaan pemeliharaan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui permintaan pemeliharaan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $permintaan = PermintaanPemeliharaan::findOrFail($id);
        
        // Hanya bisa hapus jika status DRAFT
        if ($permintaan->status_permintaan !== 'DRAFT') {
            return redirect()->route('maintenance.permintaan-pemeliharaan.index')
                ->with('error', 'Permintaan yang sudah diajukan tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $permintaan->delete();
            DB::commit();
            return redirect()->route('maintenance.permintaan-pemeliharaan.index')
                ->with('success', 'Permintaan pemeliharaan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus permintaan pemeliharaan: ' . $e->getMessage());
        }
    }

    /**
     * Ajukan permintaan (ubah status dari DRAFT ke DIAJUKAN)
     */
    public function ajukan($id)
    {
        $permintaan = PermintaanPemeliharaan::findOrFail($id);
        
        if ($permintaan->status_permintaan !== 'DRAFT') {
            return back()->with('error', 'Hanya permintaan dengan status DRAFT yang dapat diajukan.');
        }

        DB::beginTransaction();
        try {
            $permintaan->update(['status_permintaan' => 'DIAJUKAN']);
            $this->createApprovalLogs($permintaan->id_permintaan_pemeliharaan);
            
            DB::commit();
            return redirect()->route('maintenance.permintaan-pemeliharaan.show', $id)
                ->with('success', 'Permintaan pemeliharaan berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Buat approval logs untuk permintaan pemeliharaan
     */
    private function createApprovalLogs($idPermintaan)
    {
        // Ambil approval flow definition untuk PERMINTAAN_PEMELIHARAAN
        $approvalFlows = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->orderBy('step_order')
            ->get();

        foreach ($approvalFlows as $flow) {
            ApprovalLog::create([
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'id_referensi' => $idPermintaan,
                'id_approval_flow' => $flow->id,
                'user_id' => null, // Akan diisi saat approval
                'role_id' => $flow->role_id,
                'status' => 'MENUNGGU',
                'catatan' => null,
                'approved_at' => null,
            ]);
        }
    }
}


