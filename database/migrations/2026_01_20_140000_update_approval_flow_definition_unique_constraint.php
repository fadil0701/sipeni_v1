<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus unique constraint lama
        Schema::table('approval_flow_definition', function (Blueprint $table) {
            $table->dropUnique(['modul_approval', 'step_order']);
        });
        
        // Tambahkan unique constraint baru yang mempertimbangkan role_id untuk step 5
        // Untuk step selain 5, tetap unique berdasarkan modul_approval dan step_order
        // Untuk step 5, bisa memiliki multiple entry dengan role_id berbeda
        Schema::table('approval_flow_definition', function (Blueprint $table) {
            // Unique constraint untuk step selain 5
            // Note: Kita tidak bisa membuat conditional unique constraint langsung di Laravel
            // Jadi kita akan menggunakan kombinasi modul_approval, step_order, dan role_id sebagai unique
            // Untuk step yang bukan 5, role_id akan sama, jadi tetap unique
            // Untuk step 5, role_id berbeda, jadi bisa multiple
            $table->unique(['modul_approval', 'step_order', 'role_id'], 'approval_flow_unique');
        });
    }

    public function down(): void
    {
        Schema::table('approval_flow_definition', function (Blueprint $table) {
            $table->dropUnique('approval_flow_unique');
            $table->unique(['modul_approval', 'step_order']);
        });
    }
};





