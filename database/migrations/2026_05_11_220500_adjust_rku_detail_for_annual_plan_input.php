<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rku_detail')) {
            return;
        }

        Schema::table('rku_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('rku_detail', 'jenis_rku')) {
                $table->enum('jenis_rku', ['BARANG', 'ASET'])->default('BARANG')->after('id_rku');
            }
            if (!Schema::hasColumn('rku_detail', 'nama_item')) {
                $table->string('nama_item', 255)->nullable()->after('id_data_barang');
            }
        });

        Schema::table('rku_detail', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_data_barang']);
            } catch (\Throwable $e) {
                // noop
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `rku_detail` MODIFY `id_data_barang` BIGINT UNSIGNED NULL');
        }

        Schema::table('rku_detail', function (Blueprint $table) {
            try {
                $table->foreign('id_data_barang')
                    ->references('id_data_barang')
                    ->on('master_data_barang')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
                // noop
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rku_detail')) {
            return;
        }

        Schema::table('rku_detail', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_data_barang']);
            } catch (\Throwable $e) {
                // noop
            }
            if (Schema::hasColumn('rku_detail', 'nama_item')) {
                $table->dropColumn('nama_item');
            }
            if (Schema::hasColumn('rku_detail', 'jenis_rku')) {
                $table->dropColumn('jenis_rku');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `rku_detail` MODIFY `id_data_barang` BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('rku_detail', function (Blueprint $table) {
            try {
                $table->foreign('id_data_barang')
                    ->references('id_data_barang')
                    ->on('master_data_barang')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {
                // noop
            }
        });
    }
};
