<?php

namespace Database\Seeders;

use App\Models\Workflow\WorkflowDefinition;
use App\Models\Workflow\WorkflowStep;
use App\Models\Workflow\WorkflowTransition;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RkuWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $definition = WorkflowDefinition::updateOrCreate(
            ['module_key' => WorkflowEngine::MODULE_PROCUREMENT_RKU, 'code' => 'procurement_rku'],
            ['name' => 'Workflow RKU (Rencana Kebutuhan Unit)', 'is_active' => true],
        );

        $steps = [
            ['code' => 'DRAFT', 'name' => 'Draft', 'sequence' => 1, 'entity_status' => 'DRAFT', 'is_initial' => true, 'is_final' => false, 'role_name' => 'admin_unit', 'permission_name' => 'planning.rku.update'],
            ['code' => 'DIAJUKAN', 'name' => 'Diajukan', 'sequence' => 2, 'entity_status' => 'DIAJUKAN', 'is_initial' => false, 'is_final' => false, 'role_name' => null, 'permission_name' => null],
            ['code' => 'REVIEW_KASUBAG_TU', 'name' => 'Review Kasubbag TU', 'sequence' => 3, 'entity_status' => 'REVIEW_KASUBAG_TU', 'is_initial' => false, 'is_final' => false, 'role_name' => 'kasubbag_tu', 'permission_name' => 'planning.rku.approve'],
            ['code' => 'REVIEW_KEPALA_PUSAT', 'name' => 'Review Kepala Pusat', 'sequence' => 4, 'entity_status' => 'REVIEW_KEPALA_PUSAT', 'is_initial' => false, 'is_final' => false, 'role_name' => 'kepala_pusat', 'permission_name' => 'planning.rku.approve'],
            ['code' => 'APPROVED', 'name' => 'Disetujui', 'sequence' => 5, 'entity_status' => 'DISETUJUI', 'is_initial' => false, 'is_final' => true, 'role_name' => 'kepala_pusat', 'permission_name' => 'planning.rku.approve'],
            ['code' => 'REJECTED', 'name' => 'Ditolak', 'sequence' => 6, 'entity_status' => 'DITOLAK', 'is_initial' => false, 'is_final' => true, 'role_name' => null, 'permission_name' => 'planning.rku.reject'],
            ['code' => 'REVISION_REQUIRED', 'name' => 'Perlu Revisi', 'sequence' => 7, 'entity_status' => 'REVISION_REQUIRED', 'is_initial' => false, 'is_final' => false, 'role_name' => 'admin_unit', 'permission_name' => 'planning.rku.revise'],
        ];

        $stepIds = [];
        foreach ($steps as $step) {
            $model = WorkflowStep::updateOrCreate(
                [
                    'workflow_definition_id' => $definition->id,
                    'code' => $step['code'],
                ],
                [
                    'name' => $step['name'],
                    'sequence' => $step['sequence'],
                    'entity_status' => $step['entity_status'],
                    'role_name' => $step['role_name'],
                    'permission_name' => $step['permission_name'],
                    'is_initial' => $step['is_initial'],
                    'is_final' => $step['is_final'],
                ]
            );
            $stepIds[$step['code']] = $model->id;
        }

        $transitions = [
            ['from' => 'DRAFT', 'to' => 'DIAJUKAN', 'action' => 'submit', 'permission' => 'planning.rku.submit'],
            ['from' => 'DIAJUKAN', 'to' => 'DRAFT', 'action' => 'cancel', 'permission' => 'planning.rku.cancel'],
            ['from' => 'DIAJUKAN', 'to' => 'REVIEW_KASUBAG_TU', 'action' => 'review', 'permission' => 'planning.rku.approve'],
            ['from' => 'REVIEW_KASUBAG_TU', 'to' => 'REVIEW_KEPALA_PUSAT', 'action' => 'forward', 'permission' => 'planning.rku.approve'],
            ['from' => 'REVIEW_KEPALA_PUSAT', 'to' => 'APPROVED', 'action' => 'approve', 'permission' => 'planning.rku.approve'],
            ['from' => 'DIAJUKAN', 'to' => 'REJECTED', 'action' => 'reject', 'permission' => 'planning.rku.reject'],
            ['from' => 'REVIEW_KASUBAG_TU', 'to' => 'REJECTED', 'action' => 'reject', 'permission' => 'planning.rku.reject'],
            ['from' => 'REVIEW_KEPALA_PUSAT', 'to' => 'REJECTED', 'action' => 'reject', 'permission' => 'planning.rku.reject'],
            ['from' => 'REJECTED', 'to' => 'REVISION_REQUIRED', 'action' => 'request_revision', 'permission' => 'planning.rku.reject'],
            ['from' => 'REVISION_REQUIRED', 'to' => 'DRAFT', 'action' => 'revise', 'permission' => 'planning.rku.revise'],
        ];

        foreach ($transitions as $row) {
            WorkflowTransition::updateOrCreate(
                [
                    'workflow_definition_id' => $definition->id,
                    'from_step_id' => $stepIds[$row['from']],
                    'action' => $row['action'],
                ],
                [
                    'to_step_id' => $stepIds[$row['to']],
                    'permission_name' => $row['permission'],
                ]
            );
        }

        $this->command?->info('✓ RkuWorkflowSeeder: workflow procurement_rku ('.count($transitions).' transisi).');
    }
}
