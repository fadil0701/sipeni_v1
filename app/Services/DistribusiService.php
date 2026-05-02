<?php

namespace App\Services;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Models\DataStock;
use App\Models\DetailDistribusi;
use App\Models\DetailPenerimaanBarang;
use App\Models\MasterPegawai;
use App\Models\PenerimaanBarang;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DistribusiService
{
    public function __construct(
        private readonly PermintaanBarangStatusService $permintaanBarangStatus,
        private readonly StockGuardService $stockGuard
    ) {}

    public function createDraft(array $validated): TransaksiDistribusi
    {
        return DB::transaction(function () use ($validated): TransaksiDistribusi {
            $distribusi = TransaksiDistribusi::create([
                'no_sbbk' => $this->generateNoSbbk($validated['tanggal_distribusi']),
                'id_permintaan' => $validated['id_permintaan'],
                'tanggal_distribusi' => $validated['tanggal_distribusi'],
                'id_gudang_asal' => $validated['id_gudang_asal'],
                'id_gudang_tujuan' => $validated['id_gudang_tujuan'],
                'id_pegawai_pengirim' => $validated['id_pegawai_pengirim'] ?? null,
                'status_distribusi' => DistribusiStatus::Draft,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $this->syncDetails($distribusi, $validated['detail']);

            $permintaan = PermintaanBarang::find($validated['id_permintaan']);
            if ($permintaan) {
                $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::ProsesDistribusi);
            }

            return $distribusi;
        });
    }

    public function updateDraft(TransaksiDistribusi $distribusi, array $validated): void
    {
        DB::transaction(function () use ($distribusi, $validated): void {
            $distribusi->update([
                'tanggal_distribusi' => $validated['tanggal_distribusi'],
                'id_gudang_asal' => $validated['id_gudang_asal'],
                'id_gudang_tujuan' => $validated['id_gudang_tujuan'],
                'id_pegawai_pengirim' => $validated['id_pegawai_pengirim'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $this->syncDetails($distribusi, $validated['detail']);
        });
    }

    public function deleteDraft(TransaksiDistribusi $distribusi): void
    {
        DB::transaction(function () use ($distribusi): void {
            $distribusi->detailDistribusi()->delete();
            $distribusi->delete();
        });
    }

    public function markDiproses(TransaksiDistribusi $distribusi): void
    {
        $distribusi->update(['status_distribusi' => DistribusiStatus::Diproses]);
    }

    public function kirim(TransaksiDistribusi $distribusi): void
    {
        DB::transaction(function () use ($distribusi): void {
            $distribusi->loadMissing([
                'gudangTujuan.unitKerja',
                'detailDistribusi.inventory',
                'permintaan.pemohon',
            ]);

            if ($distribusi->status_distribusi === DistribusiStatus::Draft) {
                $this->markDiproses($distribusi);
            }

            $distribusi->update(['status_distribusi' => DistribusiStatus::Dikirim]);

            foreach ($distribusi->detailDistribusi as $detail) {
                $inventory = $detail->inventory;
                if (! $inventory) {
                    continue;
                }

                $context = "pengiriman distribusi {$distribusi->no_sbbk}";
                $this->stockGuard->ensureInventoryQty((int) $detail->id_inventory, (float) $detail->qty_distribusi, $context);

                if (in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'])) {
                    $this->stockGuard->ensureStockQty(
                        (int) $inventory->id_data_barang,
                        (int) $distribusi->id_gudang_asal,
                        (float) $detail->qty_distribusi,
                        $context
                    );

                    $stockAsal = DataStock::where('id_data_barang', $inventory->id_data_barang)
                        ->where('id_gudang', $distribusi->id_gudang_asal)
                        ->first();
                    if ($stockAsal) {
                        $stockAsal->qty_keluar += $detail->qty_distribusi;
                        $stockAsal->qty_akhir -= $detail->qty_distribusi;
                        $stockAsal->last_updated = now();
                        $stockAsal->save();
                    }
                }
            }

            $this->createAutoPenerimaan($distribusi);
            $distribusi->update(['status_distribusi' => DistribusiStatus::Selesai]);

            if ($distribusi->id_permintaan) {
                $permintaan = PermintaanBarang::find($distribusi->id_permintaan);
                if ($permintaan) {
                    $this->permintaanBarangStatus->setStatus($permintaan, PermintaanBarangStatus::Dikirim);
                }
            }
        });
    }

    private function syncDetails(TransaksiDistribusi $distribusi, array $details): void
    {
        $distribusi->detailDistribusi()->delete();
        foreach ($details as $detail) {
            DetailDistribusi::create([
                'id_distribusi' => $distribusi->id_distribusi,
                'id_inventory' => $detail['id_inventory'],
                'qty_distribusi' => $detail['qty_distribusi'],
                'id_satuan' => $detail['id_satuan'],
                'harga_satuan' => $detail['harga_satuan'],
                'subtotal' => $detail['qty_distribusi'] * $detail['harga_satuan'],
                'keterangan' => $detail['keterangan'] ?? null,
            ]);
        }
    }

    private function generateNoSbbk(string $tanggalDistribusi): string
    {
        $tahun = Carbon::parse($tanggalDistribusi)->format('Y');
        $last = TransaksiDistribusi::whereYear('tanggal_distribusi', $tahun)->orderBy('no_sbbk', 'desc')->first();
        $urut = 1;
        if ($last) {
            $parts = explode('/', (string) $last->no_sbbk);
            $urut = ((int) end($parts)) + 1;
        }

        return sprintf('SBBK/%s/%04d', $tahun, $urut);
    }

    private function createAutoPenerimaan(TransaksiDistribusi $distribusi): void
    {
        $existing = PenerimaanBarang::query()
            ->where('id_distribusi', $distribusi->id_distribusi)
            ->first();
        if ($existing) {
            return;
        }

        // Unit kerja penerima: dari gudang tujuan (unit yang dilayani), atau dari permintaan (pemohon).
        $gudangTujuan = $distribusi->gudangTujuan;
        $permintaan = $distribusi->permintaan;

        $unitKerjaId = (int) ($gudangTujuan?->id_unit_kerja ?? 0);
        if ($unitKerjaId <= 0 && $permintaan) {
            $unitKerjaId = (int) ($permintaan->id_unit_kerja ?? 0);
        }
        if ($unitKerjaId <= 0) {
            return;
        }

        $pegawaiPenerimaId = 0;
        $pemohon = $permintaan?->pemohon;
        if ($pemohon && (int) $pemohon->id_unit_kerja === $unitKerjaId) {
            $pegawaiPenerimaId = (int) $pemohon->id;
        }

        if ($pegawaiPenerimaId <= 0) {
            $pegawaiPenerimaId = (int) (MasterPegawai::query()
                ->where('id_unit_kerja', $unitKerjaId)
                ->value('id') ?? 0);
        }

        if ($pegawaiPenerimaId <= 0) {
            $pegawaiPenerimaId = (int) ($distribusi->id_pegawai_pengirim ?? 0);
        }
        if ($pegawaiPenerimaId <= 0) {
            return;
        }

        $tanggal = Carbon::parse($distribusi->tanggal_distribusi)->toDateString();
        $penerimaan = PenerimaanBarang::create([
            'no_penerimaan' => $this->generateNoPenerimaan($tanggal),
            'id_distribusi' => $distribusi->id_distribusi,
            'id_unit_kerja' => $unitKerjaId,
            'id_pegawai_penerima' => $pegawaiPenerimaId,
            'tanggal_penerimaan' => $tanggal,
            'status_penerimaan' => 'MENUNGGU_VERIFIKASI',
            'keterangan' => 'Dibuat otomatis setelah pengiriman distribusi. Menunggu verifikasi barang di unit penerima.',
        ]);

        foreach ($distribusi->detailDistribusi as $detail) {
            DetailPenerimaanBarang::create([
                'id_penerimaan' => $penerimaan->id_penerimaan,
                'id_inventory' => $detail->id_inventory,
                'qty_diterima' => $detail->qty_distribusi,
                'id_satuan' => $detail->id_satuan,
                'keterangan' => null,
            ]);
        }
    }

    private function generateNoPenerimaan(string $tanggalPenerimaan): string
    {
        $tahun = Carbon::parse($tanggalPenerimaan)->format('Y');
        $lastPenerimaan = PenerimaanBarang::query()
            ->whereYear('tanggal_penerimaan', $tahun)
            ->orderBy('no_penerimaan', 'desc')
            ->first();

        $urut = 1;
        if ($lastPenerimaan) {
            $parts = explode('/', $lastPenerimaan->no_penerimaan);
            $urut = ((int) end($parts)) + 1;
        }

        return sprintf('TERIMA/%s/%04d', $tahun, $urut);
    }
}
