<?php

namespace App\Services;

use App\Enums\PemeliharaanJenisPelaksana;
use App\Enums\PemeliharaanRekomendasi;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\MasterPegawai;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use App\Models\ServiceReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PemeliharaanWorkflowService
{
    public function __construct(
        private readonly ApprovalService $approvalService
    ) {}

    public function flowStep(int $stepOrder): ?ApprovalFlowDefinition
    {
        return ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->where('step_order', $stepOrder)
            ->when($stepOrder > 1 && $stepOrder !== 5, fn ($q) => $q->whereNotNull('role_id'))
            ->first();
    }

    /**
     * Pengurus Barang: disposisi ke pelaksana (teknisi/vendor).
     */
    public function disposisiPelaksana(int $approvalId, User $actor, array $payload): ApprovalLog
    {
        return DB::transaction(function () use ($approvalId, $actor, $payload) {
            $approval = ApprovalLog::with('approvalFlow.role')->findOrFail($approvalId);
            if ($approval->modul_approval !== 'PERMINTAAN_PEMELIHARAAN') {
                throw new \RuntimeException('Approval bukan modul pemeliharaan.');
            }
            if ((int) ($approval->approvalFlow?->step_order ?? 0) !== 4) {
                throw new \RuntimeException('Step disposisi Pengurus Barang tidak valid.');
            }
            if ($approval->status !== 'MENUNGGU') {
                throw new \RuntimeException('Approval ini sudah diproses.');
            }

            $jenis = PemeliharaanJenisPelaksana::from((string) $payload['jenis_pelaksana']);
            $permintaan = PermintaanPemeliharaan::findOrFail($approval->id_referensi);

            $idPegawai = $payload['id_pegawai_pelaksana'] ?? null;
            $namaVendor = $payload['nama_vendor'] ?? null;

            if ($jenis->requiresVendorName() && blank($namaVendor)) {
                throw new \RuntimeException('Nama vendor / kontrak service wajib diisi.');
            }
            if (! $jenis->requiresVendorName() && blank($idPegawai)) {
                throw new \RuntimeException('Pegawai pelaksana wajib dipilih.');
            }
            if ($idPegawai) {
                MasterPegawai::findOrFail($idPegawai);
            }

            $permintaan->update([
                'jenis_pelaksana' => $jenis->value,
                'id_pegawai_pelaksana' => $idPegawai,
                'nama_vendor' => $namaVendor,
                'disposisi_catatan' => $payload['disposisi_catatan'] ?? null,
                'status_permintaan' => 'DIPROSES',
            ]);

            $this->approvalService->approve($approval, $actor, $payload['disposisi_catatan'] ?? 'Disposisi ke '.$jenis->label());

            // Catat step pelaksanaan tanpa approval_log (role_id step 5 null / informasional).
            // Status dokumen sudah DIPROSES — Service Report dibuat dari menu terkait.

            return $approval->fresh(['approvalFlow', 'user']);
        });
    }

    /**
     * Setelah Service Report selesai dikerjakan: mulai rantai diketahui (step 6).
     */
    public function startServiceReportAcknowledgement(ServiceReport $report): void
    {
        $permintaan = $report->permintaanPemeliharaan;
        if (! $permintaan) {
            return;
        }

        $permintaan->update([
            'rekomendasi_akhir' => $report->rekomendasi,
            'status_permintaan' => 'MENUNGGU_DIKETAHUI_SR',
        ]);

        $step6 = $this->flowStep(6);
        if (! $step6) {
            throw new \RuntimeException('Flow step Diketahui SR Pengurus Barang belum dikonfigurasi.');
        }

        $this->approvalService->createPendingLog(
            $step6,
            'PERMINTAAN_PEMELIHARAAN',
            (int) $permintaan->id_permintaan_pemeliharaan,
            'Service Report '.$report->no_service_report.' siap diketahui. Rekomendasi: '.$report->rekomendasi
        );
    }

    /**
     * Setelah step 8 (Kepala Pusat mengetahui SR): cabang berdasarkan rekomendasi.
     */
    public function resolveAfterServiceReportKnown(PermintaanPemeliharaan $permintaan, ApprovalLog $approvalLog): void
    {
        $rekomendasi = PemeliharaanRekomendasi::tryFrom((string) $permintaan->rekomendasi_akhir);
        if (! $rekomendasi) {
            throw new \RuntimeException('Rekomendasi Service Report belum diisi.');
        }

        if ($rekomendasi->isClosing()) {
            $permintaan->update(['status_permintaan' => 'SELESAI']);

            return;
        }

        if ($rekomendasi === PemeliharaanRekomendasi::TidakBisaDiperbaiki) {
            $permintaan->update(['status_permintaan' => 'DIKEMBALIKAN_PENGURUS']);
            $register = RegisterAset::find($permintaan->id_register_aset);
            if ($register) {
                $register->update(['kondisi_aset' => 'TIDAK_BISA_DIPERBAIKI']);
            }

            return;
        }

        // PENDING_SPAREPART:
        // Kepala Pusat langsung mengetahui sekaligus menyetujui/menolak pembelian pada step 8.
        // Maka: skip step 9 dan langsung buat disposisi pengadaan (step 10).
        $actor = $approvalLog->user
            ?? User::find($approvalLog->user_id)
            ?? auth()->user();

        $this->createPengadaanDisposisi($permintaan, $actor, $approvalLog->catatan);
    }

    /**
     * Setelah Kepala Pusat setujui pembelian (step 9): buat disposisi pengadaan (step 10).
     */
    public function createPengadaanDisposisi(PermintaanPemeliharaan $permintaan, User $actor, ?string $catatan = null): void
    {
        $step10 = $this->flowStep(10);
        if (! $step10) {
            throw new \RuntimeException('Flow Disposisi Pengadaan belum dikonfigurasi.');
        }

        $log = $this->approvalService->createPendingLog(
            $step10,
            'PERMINTAAN_PEMELIHARAAN',
            (int) $permintaan->id_permintaan_pemeliharaan,
            $catatan ?? 'Disposisi ke Pengadaan sesuai Service Report'
        );

        // Langsung tandai DIDISPOSISIKAN agar muncul di riwayat; pengadaan memproses di modul pengadaan.
        if ($log->status === 'MENUNGGU') {
            $log->update([
                'status' => 'DIDISPOSISIKAN',
                'user_id' => $actor->id,
                'approved_at' => now(),
                'catatan' => $catatan ?? $log->catatan,
            ]);
        }

        $permintaan->update(['status_permintaan' => 'MENUNGGU_PENGADAAN']);
    }

    /**
     * Setelah pembelian spare part selesai: kembali ke pengerjaan teknisi (SR berikutnya).
     */
    public function lanjutPerbaikanSetelahPembelian(PermintaanPemeliharaan $permintaan): void
    {
        if ($permintaan->status_permintaan !== 'MENUNGGU_PENGADAAN') {
            throw new \RuntimeException('Status permintaan belum menunggu pengadaan / pembelian.');
        }

        if ($permintaan->rekomendasi_akhir !== PemeliharaanRekomendasi::PendingSparepart->value) {
            throw new \RuntimeException('Lanjut perbaikan hanya untuk rekomendasi pending spare part.');
        }

        if ($permintaan->hasOpenServiceReport()) {
            throw new \RuntimeException('Masih ada Service Report yang belum selesai.');
        }

        $permintaan->update(['status_permintaan' => 'DIPROSES']);
    }
}
