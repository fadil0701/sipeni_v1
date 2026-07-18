<?php

namespace App\Http\Controllers\Transaction;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\TransaksiDistribusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class DraftDistribusiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $kategoriGudang = strtoupper((string) $request->query('kategori', ''));
        $viewType = $request->query('view_type', 'perlu_diproses');

        $isAdmin = (UserScope::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user));
        $isViewOnly = !$isAdmin && $user->hasAnyRole(['kepala_unit', 'kepala_pusat', 'kasubbag_tu']);

        $roleByKategori = [
            'ASET' => 'admin_gudang_aset',
            'PERSEDIAAN' => 'admin_gudang_persediaan',
            'FARMASI' => 'admin_gudang_farmasi',
        ];

        $allowedRoleNames = [];
        if ($isAdmin) {
            $allowedRoleNames = array_values($roleByKategori);
        } else {
            foreach ($roleByKategori as $kategori => $roleName) {
                if ($user->hasRole($roleName)) {
                    $allowedRoleNames[] = $roleName;
                }
            }

            // Untuk role view-only, tampilkan semua kategori sebagai monitoring.
            if ($isViewOnly) {
                $allowedRoleNames = array_values($roleByKategori);
            }
        }

        // Jika user tidak termasuk role disposisi/view, kosongkan hasil agar aman.
        if (empty($allowedRoleNames)) {
            $approvalLogs = ApprovalLog::query()->whereRaw('1 = 0')->paginate(10);
            return view('transaction.draft-distribusi.index', compact('approvalLogs', 'kategoriGudang', 'viewType', 'isAdmin', 'isViewOnly'));
        }

        $query = ApprovalLog::with([
            'approvalFlow.role',
            'permintaan.unitKerja',
            'permintaan.pemohon',
        ])
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->whereHas('approvalFlow', function ($q) use ($allowedRoleNames) {
                $q->where('step_order', 4)
                    ->whereHas('role', function ($rq) use ($allowedRoleNames) {
                        $rq->whereIn('name', $allowedRoleNames);
                    });
            });

        if (isset($roleByKategori[$kategoriGudang])) {
            $roleName = $roleByKategori[$kategoriGudang];
            $query->whereHas('approvalFlow.role', fn ($q) => $q->where('name', $roleName));
        }

        // Perlu Diproses = belum ditindaklanjuti; Riwayat = hanya yang sudah selesai.
        if ($viewType === 'riwayat') {
            $query->where('status', 'SELESAI');
        } else {
            $query->where('status', 'MENUNGGU');
        }

        $allLogs = $query->latest('created_at')->get();

        // Filter final: hanya tampilkan log disposisi yang benar-benar sesuai kategori jenis_permintaan.
        $approvalLogsCollection = $allLogs->filter(function ($log) {
            $roleName = $log->approvalFlow?->role?->name;
            $permintaan = $log->permintaan;
            if (!$roleName || !$permintaan) {
                return false;
            }

            $roleKategoriMap = [
                'admin_gudang_aset' => 'ASET',
                'admin_gudang_persediaan' => 'PERSEDIAAN',
                'admin_gudang_farmasi' => 'FARMASI',
            ];

            $kategori = $roleKategoriMap[$roleName] ?? null;
            if (!$kategori) {
                return false;
            }

            $jenis = is_array($permintaan->jenis_permintaan)
                ? $permintaan->jenis_permintaan
                : (json_decode($permintaan->jenis_permintaan, true) ?? []);

            return in_array($kategori, $jenis, true);
        })->values();

        if ($viewType === 'riwayat') {
            if ($request->filled('tanggal_mulai')) {
                $tanggalMulai = $request->tanggal_mulai;
                $approvalLogsCollection = $approvalLogsCollection->filter(fn ($log) => optional($log->updated_at)?->toDateString() >= $tanggalMulai)->values();
            }
            if ($request->filled('tanggal_akhir')) {
                $tanggalAkhir = $request->tanggal_akhir;
                $approvalLogsCollection = $approvalLogsCollection->filter(fn ($log) => optional($log->updated_at)?->toDateString() <= $tanggalAkhir)->values();
            }
        }

        $page = max((int) $request->query('page', 1), 1);
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $total = $approvalLogsCollection->count();
        $items = $approvalLogsCollection->slice(($page - 1) * $perPage, $perPage)->values();

        $approvalLogs = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('transaction.draft-distribusi.index', compact('approvalLogs', 'kategoriGudang', 'viewType', 'isAdmin', 'isViewOnly'));
    }

    public function create($approvalLogId)
    {
        $approvalLog = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->findOrFail($approvalLogId);

        return redirect()->route('transaction.distribusi.create', [
            'approval_log' => $approvalLogId,
            'permintaan_id' => $approvalLog->id_referensi,
        ]);
    }

    public function store(Request $request)
    {
        return redirect()->route('transaction.distribusi.index')
            ->with('error', 'Gunakan modul Distribusi untuk membuat SPPB.');
    }

    /**
     * Riwayat "Lihat Detail" memakai id approval_log, bukan id_distribusi.
     * Resolve SBBK terkait (permintaan + kategori gudang) lalu arahkan ke detail distribusi.
     */
    public function show($id)
    {
        $approvalLog = ApprovalLog::with(['approvalFlow.role', 'permintaan'])
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->findOrFail($id);

        $permintaanId = (int) $approvalLog->id_referensi;
        if ($permintaanId <= 0) {
            abort(404, 'Permintaan terkait tidak ditemukan.');
        }

        $roleKategoriMap = [
            'admin_gudang_aset' => 'ASET',
            'admin_gudang_persediaan' => 'PERSEDIAAN',
            'admin_gudang_farmasi' => 'FARMASI',
        ];
        $kategori = $roleKategoriMap[$approvalLog->approvalFlow?->role?->name] ?? null;

        $distribusiQuery = TransaksiDistribusi::query()
            ->where('id_permintaan', $permintaanId);

        if ($kategori) {
            $distribusiQuery->whereHas('gudangAsal', fn ($q) => $q->where('kategori_gudang', $kategori));
        }

        $distribusi = $distribusiQuery->latest('id_distribusi')->first();

        if ($distribusi) {
            return redirect()->route('transaction.distribusi.show', $distribusi->id_distribusi);
        }

        return redirect()->route('transaction.permintaan-barang.show', $permintaanId)
            ->with('info', 'SBBK untuk disposisi ini belum ditemukan. Menampilkan detail permintaan.');
    }
}
