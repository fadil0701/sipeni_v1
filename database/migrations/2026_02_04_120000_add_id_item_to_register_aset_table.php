<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('register_aset', function (Blueprint $table) {
            $table->foreignId('id_item')->nullable()->after('id_inventory')->constrained('inventory_item', 'id_item')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('register_aset', function (Blueprint $table) {
            $table->dropForeign(['id_item']);
            $table->dropColumn('id_item');
        });
    }
};
