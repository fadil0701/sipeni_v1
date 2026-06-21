<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_pegawai')) {
            Schema::table('master_pegawai', function (Blueprint $table): void {
                if (! Schema::hasColumn('master_pegawai', 'nip')) {
                    $table->string('nip')->nullable()->after('nip_pegawai');
                }
                if (! Schema::hasColumn('master_pegawai', 'nama')) {
                    $table->string('nama')->nullable()->after('nama_pegawai');
                }
                if (! Schema::hasColumn('master_pegawai', 'email')) {
                    $table->string('email')->nullable()->after('email_pegawai');
                }
                if (! Schema::hasColumn('master_pegawai', 'jabatan')) {
                    $table->string('jabatan')->nullable()->after('id_jabatan');
                }
                if (! Schema::hasColumn('master_pegawai', 'unit_kerja_id')) {
                    $table->unsignedBigInteger('unit_kerja_id')->nullable()->after('id_unit_kerja');
                }
                if (! Schema::hasColumn('master_pegawai', 'is_user')) {
                    $table->boolean('is_user')->default(false)->after('user_id');
                }
                if (! Schema::hasColumn('master_pegawai', 'status_pegawai')) {
                    $table->string('status_pegawai')->default('aktif')->after('is_user');
                }
            });

            DB::table('master_pegawai')->update([
                'nip' => DB::raw('COALESCE(nip, nip_pegawai)'),
                'nama' => DB::raw('COALESCE(nama, nama_pegawai)'),
                'email' => DB::raw('COALESCE(email, email_pegawai)'),
                'unit_kerja_id' => DB::raw('COALESCE(unit_kerja_id, id_unit_kerja)'),
                'is_user' => DB::raw('CASE WHEN user_id IS NULL THEN 0 ELSE 1 END'),
            ]);
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'pegawai_id')) {
                    $table->unsignedBigInteger('pegawai_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('users', 'username')) {
                    $table->string('username')->nullable()->after('pegawai_id');
                }
                if (! Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('password');
                }
                if (! Schema::hasColumn('users', 'last_login')) {
                    $table->timestamp('last_login')->nullable()->after('is_active');
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table): void {
                if (! Schema::hasColumn('roles', 'kode_role')) {
                    $table->string('kode_role')->nullable()->after('id');
                }
                if (! Schema::hasColumn('roles', 'nama_role')) {
                    $table->string('nama_role')->nullable()->after('display_name');
                }
                if (! Schema::hasColumn('roles', 'level_akses')) {
                    $table->enum('level_akses', ['pusat', 'unit'])->default('unit')->after('name');
                }
                if (! Schema::hasColumn('roles', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
            });

            DB::table('roles')->update([
                'kode_role' => DB::raw('COALESCE(kode_role, name)'),
                'nama_role' => DB::raw('COALESCE(nama_role, display_name, name)'),
            ]);
        }

        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table): void {
                if (! Schema::hasColumn('permissions', 'modul')) {
                    $table->string('modul')->nullable()->after('module');
                }
                if (! Schema::hasColumn('permissions', 'aksi')) {
                    $table->string('aksi')->nullable()->after('modul');
                }
                if (! Schema::hasColumn('permissions', 'kode_permission')) {
                    $table->string('kode_permission')->nullable()->after('aksi');
                }
                if (! Schema::hasColumn('permissions', 'nama_permission')) {
                    $table->string('nama_permission')->nullable()->after('display_name');
                }
            });

            DB::table('permissions')->update([
                'modul' => DB::raw('COALESCE(modul, module)'),
                'kode_permission' => DB::raw('COALESCE(kode_permission, name)'),
                'nama_permission' => DB::raw('COALESCE(nama_permission, display_name, name)'),
            ]);
        }

        if (! Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->unsignedBigInteger('unit_kerja_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['user_id', 'role_id', 'unit_kerja_id'], 'user_roles_unique_assignment');
                $table->index(['role_id', 'unit_kerja_id'], 'user_roles_role_scope_index');
            });
        }

        if (! Schema::hasTable('workflow_status')) {
            Schema::create('workflow_status', function (Blueprint $table): void {
                $table->id();
                $table->string('kode_status')->unique();
                $table->string('nama_status');
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('workflow_permissions')) {
            Schema::create('workflow_permissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('workflow_status_id')->constrained('workflow_status')->cascadeOnDelete();
                $table->boolean('can_create')->default(false);
                $table->boolean('can_approve')->default(false);
                $table->boolean('can_reject')->default(false);
                $table->boolean('can_verify')->default(false);
                $table->boolean('can_process')->default(false);
                $table->boolean('can_finish')->default(false);
                $table->timestamps();
                $table->unique(['role_id', 'workflow_status_id'], 'workflow_permissions_role_status_unique');
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action');
                $table->string('table_name')->nullable();
                $table->unsignedBigInteger('data_id')->nullable();
                $table->longText('before_data')->nullable();
                $table->longText('after_data')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['action', 'table_name'], 'audit_logs_action_table_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('workflow_permissions');
        Schema::dropIfExists('workflow_status');
        Schema::dropIfExists('user_roles');
    }
};
