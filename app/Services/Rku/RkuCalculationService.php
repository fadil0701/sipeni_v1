<?php

namespace App\Services\Rku;

use App\Models\RkuHeader;
use App\Models\RkuDetail;
use App\Models\MasterRekeningBelanja;

class RkuCalculationService
{
    public function calculateTotal(RkuHeader $rku): float
    {
        return $rku->rkuDetail()
            ->whereNull('deleted_at')
            ->sum('subtotal_rencana');
    }

    public function calculateRemainingBudget(RkuHeader $rku): ?float
    {
        if (!$rku->id_rekening_belanja) {
            return null;
        }

        $rekening = MasterRekeningBelanja::find($rku->id_rekening_belanja);
        
        if (!$rekening) {
            return null;
        }

        $used = $this->getUsedBudget($rku);
        
        return $rekening->pagu_anggaran - $used;
    }

    public function getUsedBudget(RkuHeader $excludeRku = null): float
    {
        $query = RkuHeader::where('status_rku', RkuHeader::STATUS_DISETUJUI)
            ->where('id_rekening_belanja', $excludeRku?->id_rekening_belanja);

        if ($excludeRku) {
            $query->where('id_rku', '!=', $excludeRku->id_rku);
        }

        return $query->sum('total_anggaran');
    }

    public function calculateBudgetUtilization(RkuHeader $rku): ?array
    {
        if (!$rku->id_rekening_belanja) {
            return null;
        }

        $rekening = MasterRekeningBelanja::find($rku->id_rekening_belanja);
        
        if (!$rekening) {
            return null;
        }

        $pagu = $rekening->pagu_anggaran;
        $total = $rku->total_anggaran;

        return [
            'pagu' => $pagu,
            'used' => $total,
            'remaining' => $pagu - $total,
            'percentage' => $pagu > 0 ? round(($total / $pagu) * 100, 2) : 0,
            'is_exceeded' => $total > $pagu,
        ];
    }

    public function calculateYearlyTotal(string $tahun): array
    {
        return RkuHeader::where('tahun_anggaran', $tahun)
            ->selectRaw('
                SUM(CASE WHEN status_rku = ? THEN total_anggaran ELSE 0 END) as disetujui,
                SUM(CASE WHEN status_rku = ? THEN total_anggaran ELSE 0 END) as diajukan,
                SUM(CASE WHEN status_rku = ? THEN total_anggaran ELSE 0 END) as draft,
                SUM(total_anggaran) as total
            ', [
                RkuHeader::STATUS_DISETUJUI,
                RkuHeader::STATUS_DIAJUKAN,
                RkuHeader::STATUS_DRAFT
            ])
            ->first()
            ->toArray();
    }

    public function calculateUnitSummary(int $unitId, string $tahun): array
    {
        return RkuHeader::where('id_unit_kerja', $unitId)
            ->where('tahun_anggaran', $tahun)
            ->selectRaw('
                COUNT(*) as total_rku,
                SUM(CASE WHEN status_rku = ? THEN 1 ELSE 0 END) as disetujui_count,
                SUM(CASE WHEN status_rku = ? THEN 1 ELSE 0 END) as diajukan_count,
                SUM(CASE WHEN status_rku = ? THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN status_rku = ? THEN 1 ELSE 0 END) as ditolak_count,
                SUM(total_anggaran) as total_anggaran
            ', [
                RkuHeader::STATUS_DISETUJUI,
                RkuHeader::STATUS_DIAJUKAN,
                RkuHeader::STATUS_DRAFT,
                RkuHeader::STATUS_DITOLAK
            ])
            ->first()
            ->toArray();
    }

    public function calculateItemSummary(RkuHeader $rku): array
    {
        $details = $rku->rkuDetail()
            ->with(['dataBarang:id_data_barang,kode_barang,nama_barang', 'satuan:id_satuan,nama_satuan'])
            ->get();

        $summary = [
            'total_items' => $details->count(),
            'total_qty' => $details->sum('qty_rencana'),
            'total_nilai' => $details->sum('subtotal_rencana'),
            'highest_item' => null,
            'items' => [],
        ];

        foreach ($details as $detail) {
            $summary['items'][] = [
                'id' => $detail->id_rku_detail,
                'kode_barang' => $detail->dataBarang?->kode_barang,
                'nama_barang' => $detail->nama_item ?? $detail->dataBarang?->nama_barang,
                'satuan' => $detail->satuan?->nama_satuan,
                'qty' => $detail->qty_rencana,
                'harga' => $detail->harga_satuan_rencana,
                'subtotal' => $detail->subtotal_rencana,
            ];
        }

        if ($details->isNotEmpty()) {
            $highest = $details->sortByDesc('subtotal_rencana')->first();
            $summary['highest_item'] = [
                'nama' => $highest->nama_item ?? $highest->dataBarang?->nama_barang,
                'nilai' => $highest->subtotal_rencana,
            ];
        }

        return $summary;
    }

    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatPercentage(float $value): string
    {
        return round($value, 2) . '%';
    }
}