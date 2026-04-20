<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_item', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_item', 'foto_barang')) {
                $table->string('foto_barang')->nullable()->after('qr_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_item', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_item', 'foto_barang')) {
                $table->dropColumn('foto_barang');
            }
        });
    }
};

