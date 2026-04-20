<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_inventory', function (Blueprint $table) {
            $table->string('jenis_barang', 50)->nullable()->after('jenis_inventory');
        });
    }

    public function down(): void
    {
        Schema::table('data_inventory', function (Blueprint $table) {
            $table->dropColumn('jenis_barang');
        });
    }
};
