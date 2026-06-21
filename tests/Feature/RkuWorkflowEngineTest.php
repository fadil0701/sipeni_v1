<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\MasterUnitKerja;
use App\Models\Permission;
use App\Models\RkuHeader;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow\WorkflowHistory;
use App\Services\Audit\AuditLogService;
use App\Services\Rku\RkuWorkflowService;
use App\Services\Workflow\WorkflowEngine;
use Database\Seeders\RkuWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RkuWorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function migrateFreshUsing(): array
    {
        return [
            '--path' => 'database/migrations/testing/2026_05_17_200000_testing_core_and_rku_workflow.php',
            '--drop-views' => false,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RkuWorkflowSeeder::class);
    }

    private function userWithPermissions(array $permissionNames): User
    {
        $role = Role::create([
            'name' => 'rku_wf_'.uniqid(),
            'guard_name' => 'web',
            'display_name' => 'RKU Workflow Test',
        ]);

        foreach ($permissionNames as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['display_name' => $name, 'module' => 'planning', 'group' => 'rku', 'sort_order' => 0],
            );
            $role->givePermissionTo($perm);
        }

        $user = User::factory()->create();
        $user->syncUnifiedRoles([$role->id]);

        return $user;
    }

    private function createRku(string $status = RkuHeader::STATUS_DRAFT): RkuHeader
    {
        $unit = MasterUnitKerja::create([
            'kode_unit_kerja' => 'UK-'.uniqid(),
            'nama_unit_kerja' => 'Unit Test',
        ]);

        return RkuHeader::create([
            'id_unit_kerja' => $unit->id_unit_kerja,
            'no_rku' => 'RKU-'.uniqid(),
            'tahun_anggaran' => (string) date('Y'),
            'tanggal_pengajuan' => now()->toDateString(),
            'jenis_rku' => RkuHeader::JENIS_BARANG,
            'status_rku' => $status,
            'total_anggaran' => 0,
        ]);
    }

    public function test_workflow_initial_state_is_draft(): void
    {
        $rku = $this->createRku();
        $engine = new WorkflowEngine;

        $step = $engine->getCurrentStep($rku);

        $this->assertNotNull($step);
        $this->assertSame('DRAFT', $step->code);
    }

    public function test_valid_transition_submit_succeeds(): void
    {
        $user = $this->userWithPermissions(['planning.rku.submit']);
        $rku = $this->createRku();
        $service = new RkuWorkflowService;

        $this->actingAs($user);
        $updated = $service->submit($rku, $user);

        $this->assertSame(RkuHeader::STATUS_DIAJUKAN, $updated->status_rku);
    }

    public function test_invalid_transition_blocked(): void
    {
        $user = $this->userWithPermissions(['planning.rku.approve']);
        $rku = $this->createRku();
        $service = new RkuWorkflowService;

        $this->actingAs($user);
        $this->expectException(\RuntimeException::class);
        $service->approve($rku, null, $user);
    }

    public function test_unauthorized_transition_blocked(): void
    {
        $user = $this->userWithPermissions([]);
        $rku = $this->createRku();
        $engine = new WorkflowEngine;

        $this->actingAs($user);
        $this->assertFalse($engine->canTransition($rku, 'submit', $user));
    }

    public function test_history_created_on_transition(): void
    {
        $user = $this->userWithPermissions(['planning.rku.submit']);
        $rku = $this->createRku();
        $service = new RkuWorkflowService;

        $this->actingAs($user);
        $service->submit($rku, $user);

        $this->assertDatabaseHas('workflow_histories', [
            'module_key' => WorkflowEngine::MODULE_PROCUREMENT_RKU,
            'entity_type' => RkuHeader::class,
            'entity_id' => $rku->id_rku,
            'action' => 'submit',
        ]);
    }

    public function test_audit_log_created_on_transition(): void
    {
        $user = $this->userWithPermissions(['planning.rku.submit']);
        $rku = $this->createRku();
        $service = new RkuWorkflowService;

        $this->actingAs($user);
        $service->submit($rku, $user);

        $this->assertDatabaseHas('activity_logs', [
            'module_key' => AuditLogService::MODULE_PROCUREMENT_RKU,
            'action' => 'rku_submitted',
            'entity_type' => RkuHeader::class,
            'entity_id' => $rku->id_rku,
        ]);
    }

    public function test_approve_flow_success(): void
    {
        $user = $this->userWithPermissions(['planning.rku.submit', 'planning.rku.approve']);
        $service = new RkuWorkflowService;

        $rku = $this->createRku();
        $this->actingAs($user);
        $rku = $service->submit($rku, $user);
        $rku = $service->startReview($rku, $user);
        $this->assertSame(RkuHeader::STATUS_REVIEW_KASUBAG_TU, $rku->status_rku);

        $rku = $service->approve($rku, 'OK', $user);
        $this->assertSame(RkuHeader::STATUS_REVIEW_KEPALA_PUSAT, $rku->status_rku);

        $rku = $service->approve($rku, 'Final', $user);
        $this->assertSame(RkuHeader::STATUS_DISETUJUI, $rku->status_rku);
    }

    public function test_reject_flow_success(): void
    {
        $user = $this->userWithPermissions(['planning.rku.submit', 'planning.rku.reject']);
        $service = new RkuWorkflowService;
        $rku = $this->createRku();

        $this->actingAs($user);
        $rku = $service->submit($rku, $user);
        $rku = $service->reject($rku, 'Tidak memenuhi', $user);

        $this->assertSame(RkuHeader::STATUS_DITOLAK, $rku->status_rku);
        $this->assertDatabaseHas('activity_logs', ['action' => 'rku_rejected']);
    }

    public function test_revision_flow_success(): void
    {
        $user = $this->userWithPermissions(['planning.rku.submit', 'planning.rku.reject', 'planning.rku.revise']);
        $service = new RkuWorkflowService;
        $rku = $this->createRku();

        $this->actingAs($user);
        $rku = $service->submit($rku, $user);
        $rku = $service->reject($rku, 'Perlu perbaikan', $user);
        $rku = $service->requestRevision($rku, 'Lengkapi dokumen', $user);

        $this->assertSame(RkuHeader::STATUS_REVISION_REQUIRED, $rku->status_rku);

        $rku = $service->revise($rku, $user);
        $this->assertSame(RkuHeader::STATUS_DRAFT, $rku->status_rku);

        $this->assertGreaterThanOrEqual(3, WorkflowHistory::where('entity_id', $rku->id_rku)->count());
    }
}
