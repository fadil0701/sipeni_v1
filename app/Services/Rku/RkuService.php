<?php

namespace App\Services\Rku;

use App\Models\RkuHeader;
use App\Models\RkuDetail;
use App\Models\RkuAuditLog;
use App\Models\MasterDataBarang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RkuService
{
    protected RkuValidationService $validationService;
    protected RkuCalculationService $calculationService;

    public function __construct(
        RkuValidationService $validationService,
        RkuCalculationService $calculationService
    ) {
        $this->validationService = $validationService;
        $this->calculationService = $calculationService;
    }

    public function createRku(array $data): RkuHeader
    {
        $this->validationService->validateHeader($data);

        DB::beginTransaction();

        try {
            $rku = RkuHeader::create([
                'id_unit_kerja' => $data['id_unit_kerja'],
                'id_sub_kegiatan' => $data['id_sub_kegiatan'] ?? null,
                'tahun_anggaran' => $data['tahun_anggaran'],
                'jenis_rku' => $data['jenis_rku'] ?? RkuHeader::JENIS_BARANG,
                'tanggal_pengajuan' => now()->toDateString(),
                'status_rku' => RkuHeader::STATUS_DRAFT,
                'id_pengaju' => $data['id_pengaju'] ?? auth()->user()->pegawai?->id,
                'keterangan' => $data['keterangan'] ?? null,
                'id_rekening_belanja' => $data['id_rekening_belanja'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'notes' => $data['notes'] ?? null,
            ]);

            if (!empty($data['details'])) {
                $this->addDetails($rku, $data['details']);
            }

            RkuAuditLog::log(
                $rku->id_rku,
                RkuAuditLog::ACTION_CREATED,
                null,
                $rku->toArray()
            );

            $rku->createVersionSnapshot('Initial creation');

            DB::commit();

            return $rku;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRku(RkuHeader $rku, array $data): RkuHeader
    {
        if (!$rku->canEdit()) {
            throw new \RuntimeException('RKU tidak dapat diubah saat ini.');
        }

        $this->validationService->validateHeader($data, $rku->id_rku);

        DB::beginTransaction();

        try {
            $oldValues = $rku->toArray();

            $allowedHeader = [
                'id_unit_kerja',
                'tahun_anggaran',
                'id_sub_kegiatankegitan',
                'jenis_rku',
                'keterangan',
                'id_rekening_belanja',
                'priority',
                'notes',
            ];
            $headerUpdates = [];
            foreach ($allowedHeader as $key) {
                if (! array_key_exists($key, $data)) {
                    continue;
                }
                $headerUpdates[$key] = $data[$key];
            }

            if ($headerUpdates !== []) {
                $rku->update($headerUpdates);
            }

            if (isset($data['details'])) {
                $this->syncDetails($rku, $data['details']);
            }

            $rku->recalculateTotal()->save();

            RkuAuditLog::log(
                $rku->id_rku,
                RkuAuditLog::ACTION_UPDATED,
                $oldValues,
                $rku->fresh()->toArray(),
                $this->getChangedFields($oldValues, $rku->fresh()->toArray())
            );

            $rku->createVersionSnapshot('Update');

            DB::commit();

            return $rku->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRku(RkuHeader $rku): bool
    {
        if (!$rku->canDelete()) {
            throw new \RuntimeException('RKU tidak dapat dihapus.');
        }

        DB::beginTransaction();

        try {
            $oldValues = $rku->toArray();

            RkuAuditLog::log(
                $rku->id_rku,
                RkuAuditLog::ACTION_DELETED,
                $oldValues,
                null
            );

            $rku->delete();

            DB::commit();

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addDetails(RkuHeader $rku, array $details): array
    {
        $this->validationService->validateDetails($details);

        $created = [];

        foreach ($details as $detail) {
            $detailData = [
                'id_rku' => $rku->id_rku,
                'jenis_rku' => $detail['jenis_rku'] ?? 'BARANG',
                'id_data_barang' => $detail['id_data_barang'] ?? null,
                'nama_item' => trim((string) ($detail['nama_item'] ?? '')),
                'qty_rencana' => $detail['qty_rencana'],
                'id_satuan' => $detail['id_satuan'],
                'harga_satuan_rencana' => $detail['harga_satuan_rencana'],
                'subtotal_rencana' => $detail['qty_rencana'] * $detail['harga_satuan_rencana'],
                'keterangan' => $detail['keterangan'] ?? null,
            ];

            $created[] = RkuDetail::create($detailData);
        }

        $rku->recalculateTotal()->save();

        return $created;
    }

    public function syncDetails(RkuHeader $rku, array $details): void
    {
        $this->validationService->validateDetails($details);

        $existingIds = $rku->rkuDetail()->pluck('id_rku_detail')->toArray();
        $newIds = collect($details)->pluck('id_rku_detail')->filter()->toArray();

        $toDelete = array_diff($existingIds, $newIds);
        $toUpdate = array_intersect($existingIds, $newIds);

        if (!empty($toDelete)) {
            RkuDetail::whereIn('id_rku_detail', $toDelete)->delete();
        }

        foreach ($details as $detail) {
            $detailData = [
                'jenis_rku' => $detail['jenis_rku'] ?? 'BARANG',
                'id_data_barang' => $detail['id_data_barang'] ?? null,
                'nama_item' => trim((string) ($detail['nama_item'] ?? '')),
                'qty_rencana' => $detail['qty_rencana'],
                'id_satuan' => $detail['id_satuan'],
                'harga_satuan_rencana' => $detail['harga_satuan_rencana'],
                'subtotal_rencana' => $detail['qty_rencana'] * $detail['harga_satuan_rencana'],
                'keterangan' => $detail['keterangan'] ?? null,
            ];

            if (!empty($detail['id_rku_detail']) && in_array($detail['id_rku_detail'], $toUpdate)) {
                RkuDetail::where('id_rku_detail', $detail['id_rku_detail'])->update($detailData);
            } else {
                $detailData['id_rku'] = $rku->id_rku;
                RkuDetail::create($detailData);
            }
        }

        $rku->recalculateTotal()->save();
    }

    public function getPaginatedList(array $filters = [], int $perPage = 15)
    {
        $query = RkuHeader::with([
            'unitKerja:id_unit_kerja,kode_unit_kerja,nama_unit_kerja',
            'subKegiatankegitan:id_sub_kegiatankegitan,kode_sub_kegiatankegitan,nama_sub_kegiatankegitan',
            'creator:id,name',
        ]);

        if (!empty($filters['status'])) {
            $query->where('status_rku', $filters['status']);
        }

        if (!empty($filters['tahun'] ?? null) || !empty($filters['tahun_anggaran'] ?? null)) {
            $tahun = $filters['tahun'] ?? $filters['tahun_anggaran'];
            $query->where('tahun_anggaran', $tahun);
        }

        if (!empty($filters['id_unit_kerja'])) {
            $query->where('id_unit_kerja', $filters['id_unit_kerja']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('no_rku', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('keterangan', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortField = $filters['sort_field'] ?? 'updated_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $query->orderBy($sortField, $sortDir)->paginate($perPage);
    }

    protected function getChangedFields(array $old, array $new): array
    {
        $changes = [];

        foreach ($old as $key => $value) {
            if (isset($new[$key]) && $value !== $new[$key]) {
                $changes[] = $key;
            }
        }

        return $changes;
    }
}