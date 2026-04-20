<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalFlowDefinition extends Model
{
    protected $table = 'approval_flow_definition';
    public $timestamps = true;

    protected $fillable = [
        'modul_approval',
        'step_order',
        'role_id',
        'nama_step',
        'status',
        'status_text',
        'is_required',
        'can_reject',
        'can_approve',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'is_required' => 'boolean',
        'can_reject' => 'boolean',
        'can_approve' => 'boolean',
    ];

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'id_approval_flow');
    }

    /**
     * Get flow definition for a specific module
     */
    public static function getFlowForModule(string $modulApproval): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('modul_approval', $modulApproval)
            ->orderBy('step_order')
            ->with('role')
            ->get();
    }

    /**
     * Get next step in flow
     */
    public function getNextStep(): ?self
    {
        return self::where('modul_approval', $this->modul_approval)
            ->where('step_order', '>', $this->step_order)
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Get previous step in flow
     */
    public function getPreviousStep(): ?self
    {
        return self::where('modul_approval', $this->modul_approval)
            ->where('step_order', '<', $this->step_order)
            ->orderBy('step_order', 'desc')
            ->first();
    }
}
