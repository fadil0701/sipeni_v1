<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema minimal untuk Feature test workflow RKU (SQLite-safe).
 * Hanya dijalankan dari RkuWorkflowEngineTest via migrateFreshUsing().
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->string('display_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system_role')->default(false);
                $table->boolean('is_protected')->default(false);
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->string('display_name')->nullable();
                $table->string('module')->nullable();
                $table->string('group')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
                $table->primary(['role_id', 'model_id', 'model_type']);
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['permission_id', 'role_id']);
            });
        }

        if (! Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('unit_kerja_id')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('master_pegawai')) {
            Schema::create('master_pegawai', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('id_unit_kerja')->nullable();
                $table->string('nama')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('master_unit_kerja')) {
            Schema::create('master_unit_kerja', function (Blueprint $table): void {
                $table->id('id_unit_kerja');
                $table->string('kode_unit_kerja')->unique();
                $table->string('nama_unit_kerja');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rku_header')) {
            Schema::create('rku_header', function (Blueprint $table): void {
                $table->id('id_rku');
                $table->unsignedBigInteger('id_unit_kerja');
                $table->string('no_rku')->unique();
                $table->string('tahun_anggaran', 4);
                $table->date('tanggal_pengajuan');
                $table->string('jenis_rku')->default('BARANG');
                $table->string('status_rku')->default('DRAFT');
                $table->decimal('total_anggaran', 15, 2)->default(0);
                $table->unsignedBigInteger('id_approver')->nullable();
                $table->date('tanggal_approval')->nullable();
                $table->text('catatan_approval')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->boolean('is_locked')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('rku_detail')) {
            Schema::create('rku_detail', function (Blueprint $table): void {
                $table->id('id_rku_detail');
                $table->unsignedBigInteger('id_rku');
                $table->string('jenis_rku')->default('BARANG');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('rku_approval_histories')) {
            Schema::create('rku_approval_histories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('id_rku');
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->string('from_status');
                $table->string('to_status');
                $table->text('notes')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('rku_versions')) {
            Schema::create('rku_versions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('id_rku');
                $table->unsignedInteger('version_number');
                $table->json('header_snapshot')->nullable();
                $table->json('details_snapshot')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('change_reason')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('module_key', 64)->index();
                $table->string('action', 64);
                $table->string('entity_type')->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('request_url')->nullable();
                $table->string('method', 16)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

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
                $table->string('entity_status', 64)->nullable();
                $table->boolean('is_initial')->default(false);
                $table->boolean('is_final')->default(false);
                $table->timestamps();
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
                $table->index(['entity_type', 'entity_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('rku_versions');
        Schema::dropIfExists('rku_approval_histories');
        Schema::dropIfExists('rku_detail');
        Schema::dropIfExists('rku_header');
        Schema::dropIfExists('master_unit_kerja');
        Schema::dropIfExists('master_pegawai');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
