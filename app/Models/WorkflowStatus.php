<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStatus extends Model
{
    protected $table = 'workflow_status';

    protected $fillable = [
        'kode_status',
        'nama_status',
        'urutan',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(WorkflowPermission::class, 'workflow_status_id');
    }
}
