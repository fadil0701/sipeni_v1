<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_definition_id',
        'code',
        'name',
        'sequence',
        'role_name',
        'permission_name',
        'entity_status',
        'is_initial',
        'is_final',
    ];

    protected function casts(): array
    {
        return [
            'is_initial' => 'boolean',
            'is_final' => 'boolean',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }
}
