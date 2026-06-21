<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowPermission extends Model
{
    protected $table = 'workflow_permissions';

    protected $fillable = [
        'role_id',
        'workflow_status_id',
        'can_create',
        'can_approve',
        'can_reject',
        'can_verify',
        'can_process',
        'can_finish',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function workflowStatus(): BelongsTo
    {
        return $this->belongsTo(WorkflowStatus::class, 'workflow_status_id');
    }
}
