<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
use App\Helpers\PermissionHelper;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\DetailPermintaanBarang;
use App\Models\PermintaanBarang;
use App\Models\Role;
use App\Models\User;
use App\Support\PermintaanBarangStock;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ApprovalPermintaanService
{
    private const GUDANG_ROLE_BY_KATEGORI = [
        'ASET' => 'admin_gudang_aset',
        'PERSEDIAAN' => 'admin_gudang_persediaan',
        'FARMASI' => 'admin_gudang_farmasi',
    ];

    public function __construct(
        private readonly ApprovalService $approvalService,
        private readonly PermintaanBarangStatusService $permintaanBarangStatus,
        private readonly PengadaanService $pengadaanService
    ) {}

    public function processAction(string $action, int $id, User $user, array $payload = []): int
    {
        $this->authorize($action, $user);

        return match ($action) {
            'mengetahui' => (function () use ($id, $user, $payload) {
                $this->mengetahui($id, $user, $payload['catatan'] ?? null);

                return $id;
            })(),
            'verifikasi' => (function () use ($id, $user, $payload) {
                $this->verifikasi($id, $user, $payload);

                return $id;
            })(),
            'kembalikan' => (function () use ($id, $user, $payload) {
                $this->kembalikan($id, $user, (string) ($payload['catatan'] ?? ''));

                return $id;
            })(),
            'approve' => (function () use ($id, $user, $payload) {
                $this->approve($id, $user, $payload['catatan'] ?? null);

                return $id;
            })(),
            'reject' => (function () use ($id, $user, $payload) {
                $this->reject($id, $user, (string) ($payload['catatan'] ?? ''));

                return $id;
            })(),
            'disposisi' => $this->disposisi($id, $user),
            default => throw new \RuntimeException('Action approval tidak dikenali.'),
        };
    }

    public function mengetahui(int $approvalId, User $user, ?string $catatan): void
    {
        $approval = ApprovalLog::with('approvalFlow')->findOrFail($approvalId);
        if ($approval->status !== 'MENUNGGU') {
            throw new \RuntimeException('Approval ini sudah diproses.');
        }
        $this->approvalService->approve($approval, $user, $catatan);
    }

    public function verifikasi(int $approvalId, User $user, array $validated): void
    {
        $approval = ApprovalLog::with('approvalFlow')->findOrFail($approvalId);
        $permintaan = PermintaanBarang::with('detailPermintaan')->find($approval->id_referensi);
        if (! $permintaan) {
            throw new \RuntimeException('Permintaan tidak ditemukan.');
        }

        DB::transaction(function () use ($approval, $user, $validated, $permintaan): void {
            if (isset($validated['koreksi_qty']) && is_array($validated['koreksi_qty'])) {
                foreach ($validated['koreksi_qty'] as $detailId => $qtyBaru) {
                    if ($qtyBaru === null) {
                        continue;
                    }
                    $detail = DetailPermintaanBarang::find($detailId);
                    if ($detail && $detail->id_permintaan == $permintaan->id_permintaan) {
                        $originalQty = $detail->qty_diminta_awal ?? $detail->qty_diminta;
                        $detail->update([
                            'qty_diminta_awal' => $originalQty,
                            'qty_diminta' => $qtyBaru,
                            'qty_disetujui' => $qtyBaru,
                        ]);
                    }
                }
            }

            // Pastikan item yang tidak dikoreksi tetap memiliki nilai qty_disetujui.
            DetailPermintaanBarang::query()
                ->where('id_permintaan', $permintaan->id_permintaan)
                ->whereNull('qty_disetujui')
                ->update([
                    'qty_diminta_awal' => DB::raw('COALESCE(qty_diminta_awal, qty_diminta)'),
                    'qty_disetujui' => DB::raw('qty_diminta'),
                ]);

            $this->approvalService->approve($approval, $user, $validated['catatan'] ?? null);

            $permintaan->refresh()->load('detailPermintaan');
            $adaItemTanpaStock = $this->hasItemsNeedingProcurement($permintaan);

            if ($adaItemTanpaStock) {
                // Stok kosong → wajib persetujuan Kepala Pusat dulu, baru disposisi pengadaan.
                $this->createKepalaPusatPendingLog($permintaan);
                $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::Diverifikasi);
            } else {
                // Stok tersedia → disposisi ke gudang sesuai kategori, tanpa jalur pengadaan.
                $this->createGudangDisposisiLogs($permintaan);
                $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::ProsesDistribusi);
            }
        });
    }

    public function kembalikan(int $approvalId, User $user, string $catatan): void
    {
        $approval = ApprovalLog::with('approvalFlow')->findOrFail($approvalId);
        $this->approvalService->reject($approval, $user, $catatan);
    }

    public function approve(int $approvalId, User $user, ?string $catatan): void
    {
        $approval = ApprovalLog::with('approvalFlow.role')->findOrFail($approvalId);
        $roleName = $approval->approvalFlow?->role?->name;

        DB::transaction(function () use ($approval, $user, $catatan, $roleName): void {
            $this->approvalService->approve($approval, $user, $catatan);

            // Setelah Kepala Pusat menyetujui → baru muncul disposisi pengadaan + paket pengadaan.
            if ($roleName === 'kepala_pusat') {
                $permintaan = PermintaanBarang::find($approval->id_referensi);
                if (! $permintaan) {
                    throw new \RuntimeException('Permintaan tidak ditemukan.');
                }

                $this->createPengadaanDisposisiLog($permintaan);
                $this->pengadaanService->createProcurement(
                    $permintaan,
                    'Disetujui Kepala Pusat: stok tidak tersedia, dilanjutkan ke pengadaan.'
                );
            }
        });
    }

    public function reject(int $approvalId, User $user, string $catatan): void
    {
        $approval = ApprovalLog::with('approvalFlow')->findOrFail($approvalId);
        $this->approvalService->reject($approval, $user, $catatan);
    }

    public function disposisi(int $id, User $user): int
    {
        $approval = ApprovalLog::with('approvalFlow')->find($id);
        if (! $approval) {
            $permintaan = PermintaanBarang::find($id);
            if (! $permintaan) {
                throw new \RuntimeException('Approval atau permintaan tidak ditemukan.');
            }
            $step3Flow = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')->where('step_order', 3)->first();
            if ($step3Flow) {
                $approval = ApprovalLog::where('modul_approval', 'PERMINTAAN_BARANG')
                    ->where('id_referensi', $permintaan->id_permintaan)
                    ->where('id_approval_flow', $step3Flow->id)
                    ->first();
            }
        }
        if (! $approval) {
            throw new \RuntimeException('Approval tidak ditemukan.');
        }

        $permintaan = PermintaanBarang::find($approval->id_referensi);
        if (! $permintaan || $permintaan->status !== PermintaanBarangStatus::Diverifikasi) {
            throw new \RuntimeException('Permintaan harus diverifikasi oleh Kasubbag TU terlebih dahulu sebelum didisposisikan.');
        }

        if ($this->hasItemsNeedingProcurement($permintaan)) {
            throw new \RuntimeException('Permintaan membutuhkan pengadaan. Menunggu persetujuan Kepala Pusat terlebih dahulu.');
        }

        DB::transaction(function () use ($permintaan): void {
            $this->createGudangDisposisiLogs($permintaan);
            $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::ProsesDistribusi);
        });

        return (int) $approval->id;
    }

    /**
     * True jika ada item tanpa master barang, stok efektif <= 0, atau qty melebihi stok.
     * Logika selaras dengan tampilan stok di halaman approval (PermintaanBarangStock).
     */
    public function hasItemsNeedingProcurement(PermintaanBarang $permintaan): bool
    {
        $permintaan->loadMissing('detailPermintaan');
        $stockData = PermintaanBarangStock::stockDataForDetails($permintaan);

        foreach ($permintaan->detailPermintaan as $detail) {
            if (! $detail->id_data_barang) {
                return true;
            }

            $available = (float) ($stockData[$detail->id_detail_permintaan]['total'] ?? 0);
            $requested = (float) ($detail->qty_disetujui ?? $detail->qty_diminta ?? 0);

            if ($available <= 0 || $requested > $available) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perbaiki permintaan yang salah diarahkan ke Kepala Pusat padahal stok tersedia.
     */
    public function repairMisroutedKepalaPusat(PermintaanBarang $permintaan): bool
    {
        if ($this->hasItemsNeedingProcurement($permintaan)) {
            return false;
        }

        $kepalaRole = Role::where('name', 'kepala_pusat')->first();
        if (! $kepalaRole) {
            return false;
        }

        $kepalaFlowIds = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->where('role_id', $kepalaRole->id)
            ->pluck('id');

        $pendingKepala = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $permintaan->id_permintaan)
            ->whereIn('id_approval_flow', $kepalaFlowIds)
            ->where('status', 'MENUNGGU')
            ->get();

        if ($pendingKepala->isEmpty()) {
            return false;
        }

        DB::transaction(function () use ($permintaan, $pendingKepala): void {
            foreach ($pendingKepala as $log) {
                $log->delete();
            }

            $this->createGudangDisposisiLogs($permintaan);
            $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::ProsesDistribusi);
        });

        return true;
    }

    private function createKepalaPusatPendingLog(PermintaanBarang $permintaan): void
    {
        $role = Role::where('name', 'kepala_pusat')->first();
        if (! $role) {
            throw new \RuntimeException('Role kepala_pusat tidak ditemukan.');
        }

        $flow = ApprovalFlowDefinition::firstOrCreate(
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $role->id,
            ],
            [
                'nama_step' => 'Persetujuan Kepala Pusat',
                'status' => 'MENUNGGU',
                'status_text' => 'Menunggu persetujuan Kepala Pusat sebelum disposisi ke Pengadaan (stok tidak tersedia)',
                'is_required' => true,
                'can_reject' => true,
                'can_approve' => true,
            ]
        );

        $this->approvalService->createPendingLog(
            $flow,
            'PERMINTAAN_BARANG',
            (int) $permintaan->id_permintaan,
            'Menunggu persetujuan Kepala Pusat karena stok tidak tersedia'
        );
    }

    private function createPengadaanDisposisiLog(PermintaanBarang $permintaan): void
    {
        $role = Role::where('name', 'pengadaan')->first();
        if (! $role) {
            throw new \RuntimeException('Role pengadaan tidak ditemukan.');
        }

        $flow = ApprovalFlowDefinition::firstOrCreate(
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $role->id,
            ],
            [
                'nama_step' => 'Didisposisikan ke Pengadaan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan mengandung item yang tidak ada di stock, didisposisikan ke Pengadaan setelah disetujui Kepala Pusat',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ]
        );

        $this->approvalService->createPendingLog(
            $flow,
            'PERMINTAAN_BARANG',
            (int) $permintaan->id_permintaan,
            'Disposisi pengadaan setelah disetujui Kepala Pusat'
        );
    }

    private function createGudangDisposisiLogs(PermintaanBarang $permintaan): void
    {
        $jenisPermintaan = is_array($permintaan->jenis_permintaan)
            ? $permintaan->jenis_permintaan
            : (json_decode((string) $permintaan->jenis_permintaan, true) ?? []);

        $kategoriGudang = array_values(array_unique(array_intersect($jenisPermintaan, ['ASET', 'PERSEDIAAN', 'FARMASI'])));
        if ($kategoriGudang === []) {
            $kategoriGudang = ['PERSEDIAAN'];
        }

        foreach ($kategoriGudang as $kategori) {
            $roleName = self::GUDANG_ROLE_BY_KATEGORI[$kategori] ?? 'admin_gudang_pusat';
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            $flow = ApprovalFlowDefinition::firstOrCreate(
                [
                    'modul_approval' => 'PERMINTAAN_BARANG',
                    'step_order' => 4,
                    'role_id' => $role->id,
                ],
                [
                    'nama_step' => 'Didisposisikan - '.$kategori,
                    'status' => 'MENUNGGU',
                    'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang '.$kategori,
                    'is_required' => false,
                    'can_reject' => false,
                    'can_approve' => false,
                ]
            );

            $this->approvalService->createPendingLog(
                $flow,
                'PERMINTAAN_BARANG',
                (int) $permintaan->id_permintaan,
                'Disposisi untuk kategori: '.$kategori
            );
        }
    }

    private function authorize(string $action, User $user): void
    {
        // Cek route name permission dulu (via CheckRole middleware)
        $route = request()->route();
        $routeName = $route ? $route->getName() : null;
        if ($routeName && PermissionHelper::canAccess($user, $routeName)) {
            return;
        }

        // Fallback: canonical permission mapping untuk internal call
        $permissionMap = [
            'mengetahui' => 'transaction.approval.mengetahui',
            'verifikasi' => 'transaction.approval.verifikasi',
            'kembalikan' => 'transaction.approval.kembalikan',
            'approve' => 'transaction.approval.approve',
            'reject' => 'transaction.approval.reject',
            'disposisi' => 'transaction.approval.disposisi',
        ];

        $permission = $permissionMap[$action] ?? null;
        if ($permission && PermissionHelper::canAccess($user, $permission)) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki hak untuk aksi ini.');
    }
}
