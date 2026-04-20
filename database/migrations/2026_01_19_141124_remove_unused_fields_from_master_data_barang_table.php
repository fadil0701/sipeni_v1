<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_data_barang', function (Blueprint $table) {
            // Check if columns exist before dropping
            $columns = Schema::getColumnListing('master_data_barang');
            $columnsToDrop = [];
            
            if (in_array('spesifikasi', $columns)) {
                $columnsToDrop[] = 'spesifikasi';
            }
            if (in_array('merk', $columns)) {
                $columnsToDrop[] = 'merk';
            }
            if (in_array('tipe', $columns)) {
                $columnsToDrop[] = 'tipe';
            }
            if (in_array('tahun_produksi', $columns)) {
                $columnsToDrop[] = 'tahun_produksi';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_data_barang', function (Blueprint $table) {
            $table->text('spesifikasi')->nullable()->after('deskripsi');
            $table->string('merk', 100)->nullable()->after('spesifikasi');
            $table->string('tipe', 100)->nullable()->after('merk');
            $table->integer('tahun_produksi')->nullable()->after('tipe');
        });
    }
};
