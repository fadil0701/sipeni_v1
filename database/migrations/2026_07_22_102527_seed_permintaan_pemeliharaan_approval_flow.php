<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('approval_flow_definition') || ! Schema::hasTable('roles')) {
            return;
        }

        $roles = [
            'kepala_unit' => Role::where('name', 'kepala_unit')->value('id'),
            'kasubbag_tu' => Role::where('name', 'kasubbag_tu')->value('id'),
            'kepala_pusat' => Role::where('name', 'kepala_pusat')->value('id'),
        ];

        // Saat migrate fresh, roles belum ada — biarkan seeder yang mengisi.
        if (! $roles['kepala_unit'] || ! $roles['kasubbag_tu'] || ! $roles['kepala_pusat']) {
            return;
        }

        // Hapus definisi rusak (role_id null) yang mungkin terbuat sebelumnya.
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->whereNull('role_id')
            ->where('step_order', '>', 1)
            ->delete();

        $flows = [
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 1,
                'role_id' => null,
                'nama_step' => 'Diajukan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan pemeliharaan telah diajukan',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => false,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 2,
                'role_id' => $roles['kepala_unit'],
                'nama_step' => 'Diketahui Unit',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Unit mengetahui permintaan pemeliharaan',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 3,
                'role_id' => $roles['kasubbag_tu'],
                'nama_step' => 'Verifikasi Kasubbag TU',
                'status' => 'MENUNGGU',
                'status_text' => 'Kasubbag TU memverifikasi permintaan pemeliharaan',
                'is_required' => true,
                'can_reject' => true,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 4,
                'role_id' => $roles['kepala_pusat'],
                'nama_step' => 'Persetujuan Kepala Pusat',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Pusat menyetujui permintaan pemeliharaan',
                'is_required' => true,
                'can_reject' => true,
                'can_approve' => true,
            ],
        ];

        $now = now();
        foreach ($flows as $flow) {
            // Satu step_order per modul untuk pemeliharaan (bukan multi-role seperti barang step 4).
            $existing = DB::table('approval_flow_definition')
                ->where('modul_approval', $flow['modul_approval'])
                ->where('step_order', $flow['step_order'])
                ->orderByDesc('role_id')
                ->first();

            if ($existing) {
                DB::table('approval_flow_definition')
                    ->where('id', $existing->id)
                    ->update(array_merge($flow, ['updated_at' => $now]));
            } else {
                DB::table('approval_flow_definition')->insert(array_merge($flow, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('approval_flow_definition')) {
            return;
        }

        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->delete();
    }
};
