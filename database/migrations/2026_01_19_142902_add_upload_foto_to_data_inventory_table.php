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
        Schema::table('data_inventory', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('data_inventory', 'upload_foto')) {
            $table->string('upload_foto', 255)->nullable()->after('status_inventory');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_inventory', function (Blueprint $table) {
            $table->dropColumn('upload_foto');
        });
    }
};
