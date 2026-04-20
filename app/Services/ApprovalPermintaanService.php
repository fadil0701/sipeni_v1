<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\DataStock;
use App\Models\DetailPermintaanBarang;
use App\Models\PermintaanBarang;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ApprovalPermintaanService
{
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
        if (!$permintaan) {
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
                        $detail->update(['qty_diminta' => $qtyBaru]);
                    }
                }
            }

            $this->approvalService->approve($approval, $user, $validated['catatan'] ?? null);

            $permintaan->refresh()->load('detailPermintaan');
            $adaItemTanpaStock = false;
            foreach ($permintaan->detailPermintaan as $detail) {
                if (!$detail->id_data_barang) {
                    $adaItemTanpaStock = true;
                    break;
                }
                $totalStockPusat = DataStock::getStockPerGudangPusat($detail->id_data_barang)->sum('qty_akhir');
                if ($totalStockPusat <= 0) {
                    $adaItemTanpaStock = true;
                    break;
                }
            }

            if ($adaItemTanpaStock) {
                $this->pengadaanService->createProcurement(
                    $permintaan,
                    'Auto-generate dari verifikasi permintaan: stok tidak tersedia.'
                );
                $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::MenungguPengadaan);
            } else {
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
        $approval = ApprovalLog::with('approvalFlow')->findOrFail($approvalId);
        $this->approvalService->approve($approval, $user, $catatan);
    }

    public function reject(int $approvalId, User $user, string $catatan): void
    {
        $approval = ApprovalLog::with('approvalFlow')->findOrFail($approvalId);
        $this->approvalService->reject($approval, $user, $catatan);
    }

    public function disposisi(int $id, User $user): int
    {
        $approval = ApprovalLog::with('approvalFlow')->find($id);
        if (!$approval) {
            $permintaan = PermintaanBarang::find($id);
            if (!$permintaan) {
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
        if (!$approval) {
            throw new \RuntimeException('Approval tidak ditemukan.');
        }

        $permintaan = PermintaanBarang::find($approval->id_referensi);
        if (!$permintaan || $permintaan->status !== PermintaanBarangStatus::Diverifikasi) {
            throw new \RuntimeException('Permintaan harus diverifikasi oleh Kasubbag TU terlebih dahulu sebelum didisposisikan.');
        }

        DB::transaction(function () use ($approval, $permintaan, $user): void {
            $jenisPermintaan = is_array($permintaan->jenis_permintaan) ? $permintaan->jenis_permintaan : (json_decode($permintaan->jenis_permintaan, true) ?? []);
            $kategoriGudang = array_values(array_unique(array_intersect($jenisPermintaan, ['PERSEDIAAN', 'FARMASI'])));

            foreach ($kategoriGudang as $kategori) {
                $roleName = match ($kategori) {
                    'ASET' => 'admin_gudang_aset',
                    'PERSEDIAAN' => 'admin_gudang_persediaan',
                    'FARMASI' => 'admin_gudang_farmasi',
                    default => 'admin_gudang',
                };

                $role = Role::where('name', $roleName)->first();
                if (!$role) {
                    continue;
                }

                $flow = ApprovalFlowDefinition::firstOrCreate(
                    ['modul_approval' => 'PERMINTAAN_BARANG', 'step_order' => 4, 'role_id' => $role->id],
                    ['nama_step' => 'Didisposisikan - '.$kategori, 'status' => 'MENUNGGU', 'status_text' => 'Disposisi '.$kategori]
                );

                ApprovalLog::firstOrCreate(
                    [
                        'modul_approval' => $approval->modul_approval,
                        'id_referensi' => $approval->id_referensi,
                        'id_approval_flow' => $flow->id,
                    ],
                    [
                        'user_id' => null,
                        'role_id' => $role->id,
                        'status' => 'MENUNGGU',
                        'catatan' => 'Disposisi untuk kategori: '.$kategori.' oleh '.$user->name,
                        'approved_at' => null,
                    ]
                );
            }
        });

        return (int) $approval->id;
    }

    private function authorize(string $action, User $user): void
    {
        $allowed = match ($action) {
            'mengetahui' => ['kepala_unit', 'admin'],
            'verifikasi', 'kembalikan' => ['kasubbag_tu', 'admin'],
            'approve', 'reject' => ['kepala_pusat', 'admin'],
            'disposisi' => ['admin_gudang', 'admin'],
            default => [],
        };

        if (empty($allowed) || ! $user->hasAnyRole($allowed)) {
            throw new AuthorizationException('Anda tidak memiliki hak untuk aksi ini.');
        }
    }
}
