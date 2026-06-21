<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatan_role', function (Blueprint $table) {
            $table->unsignedBigInteger('id_jabatan');
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['id_jabatan', 'role_id']);
            $table->foreign('id_jabatan')
                ->references('id_jabatan')
                ->on('master_jabatan')
                ->cascadeOnDelete();
        });

        if (Schema::hasColumn('master_jabatan', 'role_id')) {
            $rows = DB::table('master_jabatan')->whereNotNull('role_id')->get(['id_jabatan', 'role_id']);
            foreach ($rows as $row) {
                $exists = DB::table('jabatan_role')
                    ->where('id_jabatan', $row->id_jabatan)
                    ->where('role_id', $row->role_id)
                    ->exists();
                if (! $exists) {
                    DB::table('jabatan_role')->insert([
                        'id_jabatan' => $row->id_jabatan,
                        'role_id' => $row->role_id,
                    ]);
                }
            }

            Schema::table('master_jabatan', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
            });
            Schema::table('master_jabatan', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('master_jabatan', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('urutan');
        });

        foreach (DB::table('jabatan_role')->orderBy('id_jabatan')->orderBy('role_id')->get() as $p) {
            DB::table('master_jabatan')
                ->where('id_jabatan', $p->id_jabatan)
                ->whereNull('role_id')
                ->update(['role_id' => $p->role_id]);
        }

        Schema::dropIfExists('jabatan_role');

        Schema::table('master_jabatan', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }
};
