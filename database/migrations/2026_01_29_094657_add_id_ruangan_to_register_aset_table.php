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
        Schema::table('register_aset', function (Blueprint $table) {
            $table->foreignId('id_ruangan')->nullable()->after('id_unit_kerja')->constrained('master_ruangan', 'id_ruangan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('register_aset', function (Blueprint $table) {
            $table->dropForeign(['id_ruangan']);
            $table->dropColumn('id_ruangan');
        });
    }
};
