<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rku_workflow_transitions')) {
            Schema::create('rku_workflow_transitions', function (Blueprint $table) {
                $table->id('id');
                $table->string('from_status', 20);
                $table->string('to_status', 20);
                $table->string('transition_name', 100);
                $table->text('description')->nullable();
                $table->json('allowed_roles')->nullable()->comment('Role yang diperbolehkan melakukan transition ini');
                $table->json('required_permissions')->nullable()->comment('Permission yang diperlukan');
                $table->boolean('is_system')->default(false)->comment('System transition (auto) vs manual');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['from_status', 'to_status'], 'unique_transition');
            });
        }

        // Seed default transitions if empty
        $existing = DB::table('rku_workflow_transitions')->count();
        if ($existing === 0) {
            DB::table('rku_workflow_transitions')->insert([
                ['from_status' => 'DRAFT', 'to_status' => 'DIAJUKAN', 'transition_name' => 'submit', 'description' => 'Submit RKU untuk approval', 'allowed_roles' => json_encode(['perencana', 'pptk_apbd']), 'required_permissions' => json_encode(['planning.rku.submit']), 'sort_order' => 1, 'is_system' => false],
                ['from_status' => 'DIAJUKAN', 'to_status' => 'DRAFT', 'transition_name' => 'cancel', 'description' => 'Batalkan pengajuan', 'allowed_roles' => json_encode(['perencana']), 'required_permissions' => json_encode(['planning.rku.cancel']), 'sort_order' => 2, 'is_system' => false],
                ['from_status' => 'DIAJUKAN', 'to_status' => 'DIPROSES', 'transition_name' => 'start_review', 'description' => 'Mulai review RKU', 'allowed_roles' => json_encode(['kepala_unit', 'kasubbag_tu']), 'required_permissions' => json_encode(['planning.rku.approve']), 'sort_order' => 3, 'is_system' => false],
                ['from_status' => 'DIPROSES', 'to_status' => 'DISETUJUI', 'transition_name' => 'approve', 'description' => 'Setuju dan approve RKU', 'allowed_roles' => json_encode(['kepala_unit', 'kasubbag_tu', 'kepala_pusat']), 'required_permissions' => json_encode(['planning.rku.approve']), 'sort_order' => 4, 'is_system' => false],
                ['from_status' => 'DIPROSES', 'to_status' => 'DITOLAK', 'transition_name' => 'reject', 'description' => 'Tolak RKU', 'allowed_roles' => json_encode(['kepala_unit', 'kasubbag_tu', 'kepala_pusat']), 'required_permissions' => json_encode(['planning.rku.reject']), 'sort_order' => 5, 'is_system' => false],
                ['from_status' => 'DITOLAK', 'to_status' => 'DRAFT', 'transition_name' => 'revise', 'description' => 'Revisi dan kembali ke draft', 'allowed_roles' => json_encode(['perencana', 'pptk_apbd']), 'required_permissions' => json_encode(['planning.rku.update']), 'sort_order' => 6, 'is_system' => false],
                ['from_status' => 'DISETUJUI', 'to_status' => 'DRAFT', 'transition_name' => 'unapprove', 'description' => 'Batalkan persetujuan', 'allowed_roles' => json_encode(['perencana']), 'required_permissions' => json_encode(['planning.rku.unapprove']), 'sort_order' => 7, 'is_system' => false],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rku_workflow_transitions');
    }
};