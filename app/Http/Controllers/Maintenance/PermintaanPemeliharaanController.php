<?php

namespace App\Http\Controllers\Maintenance;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use App\Support\Storage\PrivateStorage;
use App\Services\ApprovalService;
use App\Services\PemeliharaanWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanPemeliharaanController extends Controller
{
    public function __construct(
        private readonly ApprovalService $approvalService,
        private readonly PemeliharaanWorkflowService $pemeliharaanWorkflow
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $viewType = $request->query('view_type', 'aktif');
        if (! in_array($viewType, ['aktif', 'riwayat'], true)) {
            $viewType = 'aktif';
        }

        $query = PermintaanPemeliharaan::with(['registerAset.inventory.dataBarang', 'unitKerja', 'pemohon']);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if (UserScope::mustScopeToUnitKerja($user)) {
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

        $riwayatStatuses = ['SELESAI', 'DITOLAK', 'DIBATALKAN'];
        if ($viewType === 'riwayat') {
            $query->whereIn('status_permintaan', $riwayatStatuses);
        } else {
            $query->whereNotIn('status_permintaan', $riwayatStatuses);
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
            $query->where(function ($q) use ($search) {
                $q->where('no_permintaan_pemeliharaan', 'like', "%{$search}%")
                    ->orWhereHas('pemohon', function ($q) use ($search) {
                        $q->where('nama_pegawai', 'like', "%{$search}%");
                    })
                    ->orWhereHas('registerAset', function ($q) use ($search) {
                        $q->where('nomor_register', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = PaginationHelper::getPerPage($request, 10);
        $permintaans = $query->latest('tanggal_permintaan')->paginate($perPage)->appends($request->query());
        $listContext = 'user';

        return view('maintenance.permintaan-pemeliharaan.index', compact(
            'permintaans',
            'unitKerjas',
            'viewType',
            'listContext'
        ));
    }

    /**
     * Antrian teknisi (menu Pemeliharaan → Daftar Permintaan):
     * permintaan yang sudah didisposisi Pengurus Barang (DIPROSES+) untuk dikerjakan / buat Service Report.
     */
    public function teknisiIndex(Request $request)
    {
        $user = Auth::user();
        $viewType = $request->query('view_type', 'aktif');
        if (! in_array($viewType, ['aktif', 'riwayat'], true)) {
            $viewType = 'aktif';
        }

        $query = PermintaanPemeliharaan::with([
            'registerAset.inventory.dataBarang',
            'unitKerja',
            'pemohon',
            'pegawaiPelaksana',
            'serviceReport',
            'serviceReports',
        ]);

        // Sudah melewati disposisi pengurus (ada pelaksana) atau status pengerjaan.
        $teknisiStatusesAktif = ['DIPROSES', 'MENUNGGU_DIKETAHUI_SR', 'MENUNGGU_PENGADAAN', 'DIKEMBALIKAN_PENGURUS'];
        $teknisiStatusesRiwayat = ['SELESAI', 'DITOLAK', 'DIBATALKAN'];

        if ($viewType === 'riwayat') {
            $query->whereIn('status_permintaan', $teknisiStatusesRiwayat);
        } else {
            $query->whereIn('status_permintaan', $teknisiStatusesAktif);
        }

        // Teknisi biasa: hanya yang ditugaskan ke dirinya (admin/cross-unit lihat semua).
        if (! UserScope::canViewCrossUnitData($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai) {
                $query->where(function ($q) use ($pegawai) {
                    $q->where('id_pegawai_pelaksana', $pegawai->id)
                        ->orWhere(function ($q2) {
                            // Vendor/kontrak: tampilkan ke user yang punya akses modul (tanpa pegawai pelaksana)
                            $q2->whereNull('id_pegawai_pelaksana')
                                ->whereIn('jenis_pelaksana', ['KONTRAK_SERVICE', 'VENDOR']);
                        });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $unitKerjas = UserScope::canViewCrossUnitData($user)
            ? MasterUnitKerja::all()
            : collect();

        if ($request->filled('unit_kerja') && UserScope::canViewCrossUnitData($user)) {
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
            $query->where(function ($q) use ($search) {
                $q->where('no_permintaan_pemeliharaan', 'like', "%{$search}%")
                    ->orWhereHas('pemohon', function ($q) use ($search) {
                        $q->where('nama_pegawai', 'like', "%{$search}%");
                    })
                    ->orWhereHas('registerAset', function ($q) use ($search) {
                        $q->where('nomor_register', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = PaginationHelper::getPerPage($request, 10);
        $permintaans = $query->latest('tanggal_permintaan')->paginate($perPage)->appends($request->query());
        $listContext = 'teknisi';

        return view('maintenance.permintaan-pemeliharaan.teknisi-index', compact(
            'permintaans',
            'unitKerjas',
            'viewType',
            'listContext'
        ));
    }

    public function create()
    {
        $user = Auth::user();

        // Filter unit kerja dan pegawai berdasarkan unit kerja user yang login
        if (UserScope::mustScopeToUnitKerja($user)) {
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
                    ->with(['inventory.dataBarang', 'inventoryItem'])
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
                ->with(['inventory.dataBarang', 'inventoryItem'])
                ->get();
        }

        return view('maintenance.permintaan-pemeliharaan.create', [
            'unitKerjas' => $unitKerjas,
            'pegawais' => $pegawais,
            'registerAsets' => $registerAsets,
            'registerAsetOptions' => $registerAsets->map(function ($aset) {
                $inv = $aset->inventory;
                $namaBarang = $inv->dataBarang->nama_barang ?? '-';

                return [
                    'id' => $aset->id_register_aset,
                    'label' => ($aset->nomor_register ?: '-').' - '.$namaBarang,
                    'merk' => $inv->merk ?? '-',
                    'tipe' => $inv->tipe ?? '-',
                    'no_seri' => $aset->inventoryItem->no_seri ?? ($inv->no_seri ?? '-'),
                ];
            })->values()->all(),
        ]);
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
            'rows.*.foto_kondisi' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $uploadedFotoPaths = [];

        DB::beginTransaction();
        try {
            $pemohon = MasterPegawai::findOrFail($validated['id_pemohon']);

            if ((int) $pemohon->id_unit_kerja !== (int) $validated['id_unit_kerja']) {
                throw new \RuntimeException('Pemohon harus berasal dari unit kerja yang sama.');
            }

            // Generate nomor permintaan berurutan secara aman untuk request paralel.
            $tahun = date('Y');
            $urutan = PermintaanPemeliharaan::nextUrutanNomorUntukTahun($tahun);
            $statusPermintaan = $validated['status_permintaan'] ?? 'DRAFT';

            foreach ($validated['rows'] as $index => $row) {
                $register = RegisterAset::with('kartuInventarisRuangan')->findOrFail($row['id_register_aset']);

                if ((int) $register->id_unit_kerja !== (int) $validated['id_unit_kerja']) {
                    throw new \RuntimeException('Unit kerja register aset tidak sesuai dengan unit kerja permintaan.');
                }
                if ($register->kartuInventarisRuangan()->count() === 0) {
                    throw new \RuntimeException('Aset belum ditempatkan di KIR, silakan lengkapi penempatan terlebih dahulu.');
                }

                $fotoPath = null;
                if ($request->hasFile("rows.{$index}.foto_kondisi")) {
                    $fotoPath = PrivateStorage::storeUploadedFile(
                        $request->file("rows.{$index}.foto_kondisi"),
                        'foto-kondisi-pemeliharaan'
                    );
                    $uploadedFotoPaths[] = $fotoPath;
                }

                $noPermintaan = 'PMH/'.$tahun.'/'.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
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
                    'foto_kondisi' => $fotoPath,
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
            foreach ($uploadedFotoPaths as $path) {
                PrivateStorage::delete($path);
            }

            return back()->withInput()->with('error', 'Gagal membuat permintaan pemeliharaan: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $permintaan = PermintaanPemeliharaan::with([
            'registerAset.inventory.dataBarang',
            'registerAset.inventoryItem',
            'unitKerja',
            'pemohon',
            'pegawaiPelaksana',
            'serviceReport',
            'kalibrasi',
            'approvalLogs.approvalFlow.role',
            'approvalLogs.user',
        ])->findOrFail($id);

        $approvalLogs = ApprovalLog::getLogsForReference('PERMINTAAN_PEMELIHARAAN', $id);

        $pendingDisposisiApprovalId = $this->pendingPengurusDisposisiApprovalIds([(int) $id])[(int) $id] ?? null;

        return view('maintenance.permintaan-pemeliharaan.show', compact(
            'permintaan',
            'approvalLogs',
            'pendingDisposisiApprovalId'
        ));
    }

    /**
     * Map id_permintaan_pemeliharaan => id approval_log step 4 (MENUNGGU) untuk tombol Proses disposisi.
     *
     * @param  list<int|string>  $permintaanIds
     * @return array<int, int>
     */
    private function pendingPengurusDisposisiApprovalIds(array $permintaanIds): array
    {
        if ($permintaanIds === []) {
            return [];
        }

        $step4FlowIds = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->where('step_order', 4)
            ->pluck('id');

        if ($step4FlowIds->isEmpty()) {
            return [];
        }

        return ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->whereIn('id_referensi', $permintaanIds)
            ->whereIn('id_approval_flow', $step4FlowIds)
            ->where('status', 'MENUNGGU')
            ->pluck('id', 'id_referensi')
            ->map(fn ($id) => (int) $id)
            ->all();
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
        if (UserScope::mustScopeToUnitKerja($user)) {
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
                    ->with(['inventory.dataBarang', 'inventoryItem'])
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
                ->with(['inventory.dataBarang', 'inventoryItem'])
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
            'foto_kondisi' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hapus_foto_kondisi' => 'nullable|boolean',
            'keterangan' => 'nullable|string',
        ]);

        $newFotoPath = null;

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
            $oldFotoPath = $permintaan->foto_kondisi;
            $fotoPath = $oldFotoPath;

            if ($request->boolean('hapus_foto_kondisi') && ! $request->hasFile('foto_kondisi')) {
                $fotoPath = null;
            }

            if ($request->hasFile('foto_kondisi')) {
                $newFotoPath = PrivateStorage::storeUploadedFile(
                    $request->file('foto_kondisi'),
                    'foto-kondisi-pemeliharaan'
                );
                $fotoPath = $newFotoPath;
            }

            $permintaan->update([
                'id_register_aset' => $request->id_register_aset,
                'id_unit_kerja' => $request->id_unit_kerja,
                'id_pemohon' => $request->id_pemohon,
                'tanggal_permintaan' => $request->tanggal_permintaan,
                'jenis_pemeliharaan' => $request->jenis_pemeliharaan,
                'prioritas' => $request->prioritas,
                'status_permintaan' => $request->status_permintaan ?? 'DRAFT',
                'deskripsi_kerusakan' => $request->deskripsi_kerusakan,
                'foto_kondisi' => $fotoPath,
                'keterangan' => $request->keterangan,
            ]);

            // Jika status berubah dari DRAFT ke DIAJUKAN, buat approval logs
            if ($oldStatus === 'DRAFT' && $request->status_permintaan === 'DIAJUKAN') {
                $this->createApprovalLogs($permintaan->id_permintaan_pemeliharaan);
            }

            DB::commit();

            if (($newFotoPath || $request->boolean('hapus_foto_kondisi')) && $oldFotoPath && $oldFotoPath !== $fotoPath) {
                PrivateStorage::delete($oldFotoPath);
            }

            return redirect()->route('maintenance.permintaan-pemeliharaan.index')
                ->with('success', 'Permintaan pemeliharaan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            PrivateStorage::delete($newFotoPath);

            return back()->withInput()->with('error', 'Gagal memperbarui permintaan pemeliharaan: '.$e->getMessage());
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
            $fotoPath = $permintaan->foto_kondisi;
            $permintaan->delete();
            DB::commit();
            PrivateStorage::delete($fotoPath);

            return redirect()->route('maintenance.permintaan-pemeliharaan.index')
                ->with('success', 'Permintaan pemeliharaan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus permintaan pemeliharaan: '.$e->getMessage());
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

            return back()->with('error', 'Gagal mengajukan permintaan: '.$e->getMessage());
        }
    }

    /**
     * Setelah pembelian spare part: kembalikan ke DIPROSES agar teknisi bisa buat SR berikutnya.
     */
    public function lanjutPerbaikan($id)
    {
        $permintaan = PermintaanPemeliharaan::findOrFail($id);

        try {
            $this->pemeliharaanWorkflow->lanjutPerbaikanSetelahPembelian($permintaan);

            return redirect()
                ->route('maintenance.daftar-permintaan-pemeliharaan.index')
                ->with('success', 'Status dikembalikan ke pengerjaan. Teknisi dapat membuat Service Report lanjutan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Buat approval log awal (step Kepala Unit) untuk permintaan pemeliharaan.
     */
    private function createApprovalLogs(int $idPermintaan): void
    {
        $flowStep2 = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->where('step_order', 2)
            ->whereNotNull('role_id')
            ->first();

        if (! $flowStep2) {
            throw new \RuntimeException(
                'Konfigurasi approval permintaan pemeliharaan belum ada. Jalankan seeder ApprovalFlowDefinitionSeeder.'
            );
        }

        $this->approvalService->createPendingLog(
            $flowStep2,
            'PERMINTAAN_PEMELIHARAAN',
            $idPermintaan
        );
    }
}
