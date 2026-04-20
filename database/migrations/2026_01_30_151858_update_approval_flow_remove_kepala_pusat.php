<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus step 4 (Kepala Pusat) dari approval_flow_definition
        $kepalaPusatRole = DB::table('roles')->where('name', 'kepala_pusat')->first();
        if ($kepalaPusatRole) {
            DB::table('approval_flow_definition')
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('step_order', 4)
                ->where('role_id', $kepalaPusatRole->id)
                ->delete();
        }
        
        // Update step_order dari 5 menjadi 4 untuk step disposisi
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 5)
            ->update(['step_order' => 4]);
        
        // Update step_order dari 6 menjadi 5 untuk step diproses
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 6)
            ->update(['step_order' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan step_order dari 5 menjadi 6 untuk step diproses
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 5)
            ->update(['step_order' => 6]);
        
        // Kembalikan step_order dari 4 menjadi 5 untuk step disposisi
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->whereNotIn('role_id', function($query) {
                $query->select('id')
                    ->from('roles')
                    ->where('name', 'kepala_pusat');
            })
            ->update(['step_order' => 5]);
        
        // Kembalikan step 4 (Kepala Pusat) - perlu dijalankan seeder lagi untuk membuat ulang
        // Migration ini tidak mengembalikan step Kepala Pusat karena memerlukan data dari seeder
    }
};
