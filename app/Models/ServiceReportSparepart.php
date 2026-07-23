<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReportSparepart extends Model
{
    protected $table = 'service_report_sparepart';

    protected $primaryKey = 'id_service_report_sparepart';

    protected $fillable = [
        'id_service_report',
        'nama_sparepart',
        'merk',
        'nomor_seri',
        'foto_path',
    ];

    public function serviceReport(): BelongsTo
    {
        return $this->belongsTo(ServiceReport::class, 'id_service_report', 'id_service_report');
    }
}
