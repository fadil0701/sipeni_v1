<?php

namespace App\Services\Rku;

use App\Models\MasterSubKegiatankegitan;
use App\Models\MasterRekeningBelanja;
use App\Models\MasterDataBarang;
use App\Models\RkuHeader;
use Illuminate\Support\Facades\Validator;

class RkuValidationService
{
    public function validateHeader(array $data, ?int $excludeId = null): void
    {
        $rules = [
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'tahun_anggaran' => 'required|digits:4|integer|min:2020|max:2100',
            'jenis_rku' => 'nullable|in:BARANG,JASA,MODAL',
            'id_rekening_belanja' => 'nullable|exists:master_rekening_belanja,id',
            'priority' => 'nullable|in:normal,urgent,cito',
            'keterangan' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        // Business logic validation
        $this->validateRekeningBelanja($data['id_rekening_belanja'] ?? null);
    }

    public function validateDetails(array $details): void
    {
        if (empty($details)) {
            throw new \InvalidArgumentException('Detail RKU minimal 1 item.');
        }

        foreach ($details as $index => $detail) {
            $this->validateDetailItem($detail, $index);
        }

        $this->validateDuplicateBarang($details);
        $this->validateQtyRencana($details);
    }

    protected function validateDetailItem(array $detail, int $index): void
    {
        $rules = [
            'jenis_rku' => 'required|in:BARANG,JASA,MODAL',
            'id_data_barang' => 'nullable|exists:master_data_barang,id_data_barang',
            'nama_item' => 'required|string|max:255',
            'qty_rencana' => 'required|numeric|min:0.0001|max:999999.9999',
            'id_satuan' => 'required|exists:master_satuan,id_satuan',
            'harga_satuan_rencana' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($detail, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                "Detail item #" . ($index + 1) . ": " . $validator->errors()->first()
            );
        }
    }

    protected function validateDuplicateBarang(array $details): void
    {
        $normalizedNames = collect($details)
            ->map(fn (array $detail) => mb_strtolower(trim((string) ($detail['nama_item'] ?? ''))))
            ->filter()
            ->values()
            ->toArray();

        if (count($normalizedNames) !== count(array_unique($normalizedNames))) {
            throw new \InvalidArgumentException('Item barang/aset duplikat ditemukan.');
        }
    }

    protected function validateQtyRencana(array $details): void
    {
        foreach ($details as $index => $detail) {
            if (isset($detail['qty_rencana']) && $detail['qty_rencana'] <= 0) {
                throw new \InvalidArgumentException(
                    "Detail item #" . ($index + 1) . ": Jumlah harus lebih dari 0."
                );
            }
        }
    }

    public function validatePaguConstraint(RkuHeader $rku): array
    {
        $warnings = [];
        $errors = [];

        $subKegiatankegitan = $rku->subKegiatankegitan;
        
        if (!$subKegiatankegitan) {
            return ['warnings' => [], 'errors' => []];
        }

        // Get pagu from rekening belanja
        if ($rku->id_rekening_belanja) {
            $rekening = MasterRekeningBelanja::find($rku->id_rekening_belanja);
            if ($rekening) {
                $pagu = $rekening->pagu_anggaran;
                
                if ($rku->total_anggaran > $pagu) {
                    $exceed = $rku->total_anggaran - $pagu;
                    $pct = ($exceed / $pagu) * 100;
                    
                    if ($pct > 10) {
                        $errors[] = "Total anggaran melebihi pagu rekening sebesar Rp " . number_format($pagu, 0, ',', '.');
                    } else {
                        $warnings[] = "Total anggaran melebihi pagu sebesar Rp " . number_format($exceed, 0, ',', '.');
                    }
                }

                // Warning if close to pagu (80%)
                if ($rku->total_anggaran >= $pagu * 0.8 && $rku->total_anggaran <= $pagu) {
                    $warnings[] = "Total anggaran sudah 80% dari pagu rekening.";
                }
            }
        }

        return [
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    public function validatePriceChange(array $details): array
    {
        $warnings = [];

        foreach ($details as $detail) {
            $barang = MasterDataBarang::find($detail['id_data_barang']);
            
            if ($barang && $barang->harga_satuan) {
                $newPrice = $detail['harga_satuan_rencana'];
                $lastPrice = $barang->harga_satuan;
                $change = $newPrice > 0 ? (($newPrice - $lastPrice) / $lastPrice) * 100 : 0;

                if (abs($change) > 50) {
                    $warnings[] = "Harga {$barang->nama_barang} berubah signifikan (" . round($change) . "%)";
                }
            }
        }

        return $warnings;
    }

    protected function validateSubKegiatankegitan(?int $id): void
    {
        if (!$id) return;

        $sub = MasterSubKegiatankegitan::find($id);
        
        if (!$sub) {
            throw new \InvalidArgumentException('Sub kegiatan tidak ditemukan.');
        }

        if (!$sub->is_active) {
            throw new \InvalidArgumentException('Sub kegiatan tidak aktif.');
        }
    }

    protected function validateRekeningBelanja(?int $id): void
    {
        if (!$id) return;

        $rekening = MasterRekeningBelanja::find($id);
        
        if ($rekening && !$rekening->is_active) {
            throw new \InvalidArgumentException('Rekening belanja tidak aktif.');
        }
    }
}