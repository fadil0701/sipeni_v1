<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermintaanPemeliharaan extends Model
{
    protected $table = 'permintaan_pemeliharaan';

    protected $primaryKey = 'id_permintaan_pemeliharaan';

    protected $fillable = [
        'no_permintaan_pemeliharaan',
        'id_register_aset',
        'id_unit_kerja',
        'id_pemohon',
        'tanggal_permintaan',
        'jenis_pemeliharaan',
        'prioritas',
        'status_permintaan',
        'deskripsi_kerusakan',
        'foto_kondisi',
        'jenis_pelaksana',
        'id_pegawai_pelaksana',
        'nama_vendor',
        'disposisi_catatan',
        'rekomendasi_akhir',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
    ];

    /**
     * Nomor urut berikutnya untuk format PMH/{tahun}/####.
     * Harus dipanggil di dalam transaksi DB; memakai lockForUpdate agar aman saat request paralel.
     */
    public static function nextUrutanNomorUntukTahun(string $tahun): int
    {
        $prefix = 'PMH/'.$tahun.'/';

        $lastNoPermintaan = static::query()
            ->where('no_permintaan_pemeliharaan', 'like', $prefix.'%')
            ->orderByDesc('id_permintaan_pemeliharaan')
            ->lockForUpdate()
            ->value('no_permintaan_pemeliharaan');

        $next = 1;
        if ($lastNoPermintaan && preg_match('/(\d{4})$/', $lastNoPermintaan, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        while (static::query()
            ->where('no_permintaan_pemeliharaan', $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT))
            ->exists()) {
            $next++;
        }

        return $next;
    }

    /**
     * Register aset yang diminta untuk pemeliharaan
     */
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Unit kerja pemohon
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    /**
     * Pegawai pemohon
     */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pemohon', 'id');
    }

    public function pegawaiPelaksana(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pegawai_pelaksana', 'id');
    }

    /**
     * Semua service report terkait (bisa lebih dari satu jika ada siklus spare part).
     */
    public function serviceReports(): HasMany
    {
        return $this->hasMany(ServiceReport::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Service report terbaru (kompatibilitas view/controller lama).
     */
    public function serviceReport(): HasOne
    {
        return $this->hasOne(ServiceReport::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan')
            ->latestOfMany('id_service_report');
    }

    public function hasOpenServiceReport(): bool
    {
        if ($this->relationLoaded('serviceReports')) {
            return $this->serviceReports
                ->whereIn('status_service', ['MENUNGGU', 'DIPROSES'])
                ->isNotEmpty();
        }

        return $this->serviceReports()
            ->whereIn('status_service', ['MENUNGGU', 'DIPROSES'])
            ->exists();
    }

    public function canStartServiceReport(): bool
    {
        return $this->status_permintaan === 'DIPROSES' && ! $this->hasOpenServiceReport();
    }

    public function canResumeRepairAfterPurchase(): bool
    {
        return $this->status_permintaan === 'MENUNGGU_PENGADAAN'
            && $this->rekomendasi_akhir === 'PENDING_SPAREPART';
    }

    /**
     * Kalibrasi yang terkait (jika jenis KALIBRASI)
     */
    public function kalibrasi(): HasOne
    {
        return $this->hasOne(KalibrasiAset::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Riwayat pemeliharaan
     */
    public function riwayatPemeliharaan(): HasMany
    {
        return $this->hasMany(RiwayatPemeliharaan::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Approval logs untuk permintaan pemeliharaan
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'id_referensi', 'id_permintaan_pemeliharaan')
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN');
    }
}
