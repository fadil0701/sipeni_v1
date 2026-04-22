<?php

namespace App\Services;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Models\DetailDistribusi;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DistribusiService
{
    public function __construct(
        private readonly PermintaanBarangStatusService $permintaanBarangStatus
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
            if ($distribusi->status_distribusi === DistribusiStatus::Draft) {
                $this->markDiproses($distribusi);
            }

            $distribusi->update(['status_distribusi' => DistribusiStatus::Dikirim]);

            foreach ($distribusi->detailDistribusi as $detail) {
                $inventory = $detail->inventory;
                if (! $inventory) {
                    continue;
                }

                if (in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'])) {
                    $stockAsal = \App\Models\DataStock::where('id_data_barang', $inventory->id_data_barang)
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
}
