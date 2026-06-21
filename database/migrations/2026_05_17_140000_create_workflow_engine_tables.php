<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workflow_definitions')) {
            Schema::create('workflow_definitions', function (Blueprint $table): void {
                $table->id();
                $table->string('module_key', 64)->index();
                $table->string('code', 64);
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['module_key', 'code']);
            });
        }

        if (! Schema::hasTable('workflow_steps')) {
            Schema::create('workflow_steps', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('workflow_definition_id')->index();
                $table->string('code', 64);
                $table->string('name');
                $table->unsignedInteger('sequence')->default(0);
                $table->string('role_name')->nullable();
                $table->string('permission_name')->nullable();
                $table->string('entity_status', 64)->nullable()->comment('Nilai status pada entity (mis. status_rku)');
                $table->boolean('is_initial')->default(false);
                $table->boolean('is_final')->default(false);
                $table->timestamps();

                $table->unique(['workflow_definition_id', 'code'], 'workflow_steps_def_code_unique');
            });
        }

        if (! Schema::hasTable('workflow_transitions')) {
            Schema::create('workflow_transitions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('workflow_definition_id')->index();
                $table->unsignedBigInteger('from_step_id')->index();
                $table->unsignedBigInteger('to_step_id')->index();
                $table->string('action', 64);
                $table->string('permission_name')->nullable();
                $table->timestamps();

                $table->unique(
                    ['workflow_definition_id', 'from_step_id', 'action'],
                    'workflow_transitions_def_from_action_unique'
                );
            });
        }

        if (! Schema::hasTable('workflow_histories')) {
            Schema::create('workflow_histories', function (Blueprint $table): void {
                $table->id();
                $table->string('module_key', 64)->index();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->unsignedBigInteger('workflow_step_id')->nullable()->index();
                $table->string('action', 64);
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->text('comment')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['entity_type', 'entity_id'], 'workflow_histories_entity_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
    }
};
