<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rku_header') || ! Schema::hasColumn('rku_header', 'id_sub_kegiatan')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            try {
                Schema::table('rku_header', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_sub_kegiatan')->nullable()->change();
                });
            } catch (\Throwable $e) {
                //
            }

            return;
        }

        try {
            Schema::table('rku_header', function (Blueprint $table) {
                $table->dropForeign(['id_sub_kegiatan']);
            });
        } catch (\Throwable $e) {
            //
        }

        DB::statement('ALTER TABLE `rku_header` MODIFY `id_sub_kegiatan` BIGINT UNSIGNED NULL');

        try {
            Schema::table('rku_header', function (Blueprint $table) {
                $table->foreign('id_sub_kegiatan')
                    ->references('id_sub_kegiatan')
                    ->on('master_sub_kegiatan')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            //
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rku_header')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        try {
            Schema::table('rku_header', function (Blueprint $table) {
                $table->dropForeign(['id_sub_kegiatan']);
            });
        } catch (\Throwable $e) {
            //
        }

        DB::statement('ALTER TABLE `rku_header` MODIFY `id_sub_kegiatan` BIGINT UNSIGNED NOT NULL');

        try {
            Schema::table('rku_header', function (Blueprint $table) {
                $table->foreign('id_sub_kegiatan')
                    ->references('id_sub_kegiatan')
                    ->on('master_sub_kegiatan')
                    ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            //
        }
    }
};
