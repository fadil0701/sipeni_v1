<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $guard = 'web';

        if (Schema::hasTable('roles') && ! Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) use ($guard) {
                $table->string('guard_name')->default($guard)->after('name');
            });
        }

        if (Schema::hasTable('permissions') && ! Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) use ($guard) {
                $table->string('guard_name')->default($guard)->after('name');
            });
        }

        DB::table('roles')->whereNull('guard_name')->update(['guard_name' => $guard]);
        DB::table('permissions')->whereNull('guard_name')->update(['guard_name' => $guard]);

        if (Schema::hasTable('roles')) {
            $this->swapUniqueNameToNameGuard('roles');
        }

        if (Schema::hasTable('permissions')) {
            $this->swapUniqueNameToNameGuard('permissions');
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();

                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->cascadeOnDelete();

                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        }

        if (Schema::hasTable('role_user') && Schema::hasTable('model_has_roles')) {
            $rows = DB::table('role_user')->select('user_id', 'role_id')->get();
            foreach ($rows as $row) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $row->role_id,
                    'model_type' => User::class,
                    'model_id' => $row->user_id,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (Schema::hasTable('model_has_permissions')) {
            Schema::dropIfExists('model_has_permissions');
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::dropIfExists('model_has_roles');
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'guard_name')) {
            $this->restoreUniqueNameOnly('permissions');
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'guard_name')) {
            $this->restoreUniqueNameOnly('roles');
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function swapUniqueNameToNameGuard(string $table): void
    {
        Schema::table($table, function (Blueprint $t) {
            try {
                $t->dropUnique(['name']);
            } catch (\Throwable) {
                //
            }
        });

        Schema::table($table, function (Blueprint $t) {
            try {
                $t->unique(['name', 'guard_name']);
            } catch (\Throwable) {
                //
            }
        });
    }

    private function restoreUniqueNameOnly(string $table): void
    {
        Schema::table($table, function (Blueprint $t) {
            try {
                $t->dropUnique(['name', 'guard_name']);
            } catch (\Throwable) {
                //
            }
        });

        Schema::table($table, function (Blueprint $t) {
            try {
                $t->unique('name');
            } catch (\Throwable) {
                //
            }
        });
    }
};
