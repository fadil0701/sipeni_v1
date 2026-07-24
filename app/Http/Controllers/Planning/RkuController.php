<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\CreateRkuRequest;
use App\Http\Requests\Planning\UpdateRkuRequest;
use App\Models\RkuHeader;
use App\Models\MasterUnitKerja;
use App\Models\MasterSatuan;
use App\Services\Rku\RkuService;
use App\Services\Rku\RkuWorkflowService;
use App\Services\Rku\RkuValidationService;
use App\Services\Rku\RkuCalculationService;
use App\Models\MasterSubKegiatankegitan;
use App\Support\Http\SafeUserMessage;
use App\Support\Rbac\UserScope;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RkuController extends Controller
{
    protected RkuService $rkuService;
    protected RkuWorkflowService $workflowService;
    protected RkuValidationService $validationService;
    protected RkuCalculationService $calculationService;

    public function __construct()
    {
        $this->validationService = new RkuValidationService();
        $this->calculationService = new RkuCalculationService();
        $this->workflowService = new RkuWorkflowService();
        $this->rkuService = new RkuService($this->validationService, $this->calculationService);
    }

    /**
     * Display a listing of the resource.
     *
     * context=unit  → RKU unit (Transaksi): hanya unit kerja sendiri, monitoring sampai selesai
     * context=daftar → Daftar RKU (Perencanaan): seluruh unit untuk tim perencana/pusat
     */
    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', RkuHeader::class)) {
            abort(403, 'Unauthorized access.');
        }

        /** @var User $user */
        $user = Auth::user();
        $context = $this->resolveRkuListContext($request, $user);

        $viewType = $request->query('view_type', 'aktif');
        if (! in_array($viewType, ['aktif', 'riwayat'], true)) {
            $viewType = 'aktif';
        }

        $filters = $request->only(['tahun_anggaran', 'id_unit_kerja', 'search']);
        if ($request->filled('status_rku')) {
            $filters['status'] = $request->input('status_rku');
        }

        $riwayatStatuses = [
            RkuHeader::STATUS_DISETUJUI,
            RkuHeader::STATUS_DITOLAK,
        ];
        if (! $request->filled('status_rku')) {
            $filters['view_type'] = $viewType;
            $filters['riwayat_statuses'] = $riwayatStatuses;
        }

        if ($context === 'unit') {
            $unitId = $user->pegawai?->id_unit_kerja;
            $filters['id_unit_kerja'] = $unitId ?: 0;
        } elseif (! $this->userCanViewAllRku($user)) {
            // Cadangan: tanpa view_all / pusat, tetap batasi ke unit sendiri
            $filters['id_unit_kerja'] = $filters['id_unit_kerja'] ?? $user->pegawai?->id_unit_kerja;
        }

        $rkus = $this->rkuService->getPaginatedList($filters, 15)->appends($request->query());

        $tahunBerjalan = (int) date('Y');
        $tahunOtomatis = collect(range($tahunBerjalan, $tahunBerjalan + 2));
        $tahunList = RkuHeader::select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->merge($tahunOtomatis)
            ->map(fn ($y) => (string) $y)
            ->unique()
            ->sortDesc()
            ->values();

        $unitKerjaList = $context === 'daftar'
            ? MasterUnitKerja::orderBy('nama_unit_kerja')->get()
            : collect();

        return view('planning.rku.index', compact('rkus', 'tahunList', 'unitKerjaList', 'viewType', 'context'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!Gate::allows('create', RkuHeader::class)) {
            abort(403, 'Unauthorized to create RKU.');
        }

        /** @var User $user */
        $user = Auth::user();
        $context = $this->resolveRkuListContext($request, $user);

        $selectedUnit = $user->pegawai?->id_unit_kerja;
        $lockUnitKerja = $context === 'unit' || ! $this->userCanViewAllRku($user);

        if ($lockUnitKerja) {
            $unitKerjaList = $selectedUnit
                ? MasterUnitKerja::where('id_unit_kerja', $selectedUnit)->get()
                : collect();
        } else {
            $unitKerjaList = MasterUnitKerja::orderBy('nama_unit_kerja')->get();
        }

        $satuanList = MasterSatuan::orderBy('nama_satuan')->get();

        return view('planning.rku.create', compact('unitKerjaList', 'selectedUnit', 'satuanList', 'context', 'lockUnitKerja'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRkuRequest $request)
    {
        if (!Gate::allows('create', RkuHeader::class)) {
            abort(403, 'Unauthorized to create RKU.');
        }

        try {
            /** @var User $user */
            $user = Auth::user();
            $data = $request->validated();
            $data['id_pengaju'] = $user->pegawai?->id;
            $data['tahun_anggaran'] = (string) ((int) date('Y') + 2);

            $context = $this->resolveRkuListContext($request, $user);
            if ($context === 'unit' || ! $this->userCanViewAllRku($user)) {
                $unitId = $user->pegawai?->id_unit_kerja;
                if (! $unitId) {
                    return back()->withInput()->with('error', 'Akun Anda belum terhubung ke unit kerja.');
                }
                $data['id_unit_kerja'] = $unitId;
            }

            $rku = $this->rkuService->createRku($data);

            return redirect()->route('planning.rku.show', $rku->id_rku)
                ->with('success', 'RKU berhasil dibuat.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', SafeUserMessage::operationFailed('menyimpan RKU'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rku = RkuHeader::with([
            'unitKerja',
            'subKegiatan.kegiatan.program',
            'pengaju',
            'approver',
            'rkuDetail.dataBarang',
            'rkuDetail.satuan',
            'creator',
            'approvalHistories.approver',
        ])->findOrFail($id);

        if (!Gate::allows('view', $rku)) {
            abort(403, 'Unauthorized to view this RKU.');
        }

        // Log view action
        \App\Models\RkuAuditLog::log($rku->id_rku, \App\Models\RkuAuditLog::ACTION_VIEWED);

        $availableTransitions = $this->workflowService->getAvailableTransitions($rku);
        $workflowHistories = $this->workflowService->getWorkflowHistory($rku);

        return view('planning.rku.show', compact('rku', 'availableTransitions', 'workflowHistories'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rku = RkuHeader::with(['rkuDetail.dataBarang', 'rkuDetail.satuan'])->findOrFail($id);

        if (!Gate::allows('update', $rku)) {
            abort(403, 'Unauthorized to edit this RKU.');
        }

        $unitKerjaList = MasterUnitKerja::orderBy('nama_unit_kerja')->get();
        $satuanList = MasterSatuan::orderBy('nama_satuan')->get();

        return view('planning.rku.edit', compact('rku', 'unitKerjaList', 'satuanList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRkuRequest $request, string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('update', $rku)) {
            abort(403, 'Unauthorized to edit this RKU.');
        }

        try {
            $data = $request->validated();
            $rku = $this->rkuService->updateRku($rku, $data);

            return redirect()->route('planning.rku.show', $rku->id_rku)
                ->with('success', 'RKU berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui RKU.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('delete', $rku)) {
            abort(403, 'Unauthorized to delete this RKU.');
        }

        try {
            $this->rkuService->deleteRku($rku);

            return redirect()->route('planning.rku.index')
                ->with('success', 'RKU berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Terjadi kesalahan saat menghapus RKU.');
        }
    }

    /**
     * Submit RKU for approval.
     */
    public function submit(string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('submit', $rku)) {
            abort(403, 'Unauthorized to submit this RKU.');
        }

        try {
            $rku = $this->workflowService->submit($rku);

            return back()->with('success', 'RKU berhasil disubmit untuk approval.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve RKU.
     */
    public function approve(Request $request, string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('approve', $rku)) {
            abort(403, 'Unauthorized to approve this RKU.');
        }

        try {
            $notes = $request->input('notes');
            $rku = $this->workflowService->approve($rku, $notes);

            return back()->with('success', 'RKU berhasil diapprove.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject RKU.
     */
    public function reject(Request $request, string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('reject', $rku)) {
            abort(403, 'Unauthorized to reject this RKU.');
        }

        $request->validate(['notes' => 'required|string|max:1000']);

        try {
            $notes = $request->input('notes');
            $rku = $this->workflowService->reject($rku, $notes);

            return back()->with('success', 'RKU ditolak.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel submitted RKU.
     */
    public function cancel(Request $request, string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('cancel', $rku)) {
            abort(403, 'Unauthorized to cancel this RKU.');
        }

        try {
            $notes = $request->input('notes');
            $rku = $this->workflowService->cancel($rku, $notes);

            return back()->with('success', 'RKU dibatalkan dan dikembalikan ke draft.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Start review process.
     */
    public function startReview(string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (!Gate::allows('approve', $rku)) {
            abort(403, 'Unauthorized to start review.');
        }

        try {
            $rku = $this->workflowService->startReview($rku);

            return back()->with('success', 'Review RKU dimulai.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Revise rejected RKU.
     */
    public function revise(Request $request, string $id)
    {
        $rku = RkuHeader::findOrFail($id);

        if (! Gate::allows('update', $rku) && ! Gate::allows('reject', $rku)) {
            abort(403, 'Unauthorized to revise this RKU.');
        }

        try {
            if ($request->boolean('request_revision')) {
                $rku = $this->workflowService->requestRevision($rku, $request->input('notes'));
                return back()->with('success', 'Permintaan revisi RKU dicatat.');
            }

            $rku = $this->workflowService->revise($rku);

            return back()->with('success', 'RKU dikembalikan ke draft untuk revisi.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rekap perencanaan tahunan.
     */
    public function rekapTahunan(Request $request)
    {
        if (!Gate::allows('viewRekap', new RkuHeader())) {
            abort(403, 'Unauthorized to view rekap.');
        }

        $tahun = $request->get('tahun', date('Y'));

        $rekap = [];
        $rkus = RkuHeader::with(['subKegiatan.kegiatan.program', 'unitKerja'])
            ->where('tahun_anggaran', $tahun)
            ->where('status_rku', RkuHeader::STATUS_DISETUJUI)
            ->orderBy('id_rku')
            ->get();

        foreach ($rkus as $rku) {
            $sub = $rku->subKegiatan;
            if (!$sub) continue;

            $kegiatan = $sub->kegiatan;
            $program = $kegiatan?->program;

            $idProgram = $program?->id_program ?? 0;
            $idKegiatan = $kegiatan?->id_kegiatan ?? 0;
            $idSub = $sub->id_sub_kegiatan;

            if (!isset($rekap[$idProgram])) {
                $rekap[$idProgram] = [
                    'nama_program' => $program?->nama_program ?? '-',
                    'kegiatankegitan' => [],
                ];
            }

            if (!isset($rekap[$idProgram]['kegiatankegitan'][$idKegiatan])) {
                $rekap[$idProgram]['kegiatankegitan'][$idKegiatan] = [
                    'nama_kegiatankegitan' => $kegiatan?->nama_kegiatan ?? '-',
                    'sub_kegiatankegitan' => [],
                ];
            }

            if (!isset($rekap[$idProgram]['kegiatankegitan'][$idKegiatan]['sub_kegiatankegitan'][$idSub])) {
                $rekap[$idProgram]['kegiatankegitan'][$idKegiatan]['sub_kegiatankegitan'][$idSub] = [
                    'nama_sub_kegiatankegitan' => $sub->nama_sub_kegiatan,
                    'kode_sub_kegiatankegitan' => $sub->kode_sub_kegiatan ?? '',
                    'jumlah_rku' => 0,
                    'total_anggaran' => 0,
                ];
            }

            $rekap[$idProgram]['kegiatankegitan'][$idKegiatan]['sub_kegiatankegitan'][$idSub]['jumlah_rku']++;
            $rekap[$idProgram]['kegiatankegitan'][$idKegiatan]['sub_kegiatankegitan'][$idSub]['total_anggaran'] += (float) $rku->total_anggaran;
        }

        $tahunList = RkuHeader::select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran');

        if ($tahunList->isEmpty()) {
            $tahunList = collect([date('Y')]);
        }

        return view('planning.rekap-tahunan', compact('rekap', 'tahun', 'tahunList'));
    }

    /**
     * Apakah user boleh melihat seluruh RKU lintas unit (Daftar RKU).
     */
    private function userCanViewAllRku(User $user): bool
    {
        if (PermissionHelper::hasEnterpriseBypassRole($user)) {
            return true;
        }

        if ($user->hasPermission('planning.rku.view_all')) {
            return true;
        }

        // Role pusat (bukan unit-scoped) dengan akses index = daftar lintas unit
        return PermissionHelper::canAccess($user, 'planning.rku.index')
            && ! UserScope::mustScopeToUnitKerja($user);
    }

    /**
     * @return 'unit'|'daftar'
     */
    private function resolveRkuListContext(Request $request, User $user): string
    {
        $requested = $request->query('context', $request->input('context'));
        $canDaftar = $this->userCanViewAllRku($user);
        $canUnit = UserScope::mustScopeToUnitKerja($user)
            || PermissionHelper::hasEnterpriseBypassRole($user);

        if ($requested === 'daftar') {
            return $canDaftar ? 'daftar' : 'unit';
        }

        if ($requested === 'unit') {
            if ($canUnit) {
                return 'unit';
            }

            return $canDaftar ? 'daftar' : 'unit';
        }

        // Default: unit-scoped → unit; pusat/perencana → daftar
        if (UserScope::mustScopeToUnitKerja($user)) {
            return 'unit';
        }

        return $canDaftar ? 'daftar' : 'unit';
    }
}