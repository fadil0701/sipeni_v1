<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: buat ulang tabel dengan status yang sudah termasuk DIPROSES
            DB::statement('CREATE TABLE approval_log_new AS SELECT * FROM approval_log');
            DB::statement('DROP TABLE approval_log');
            
            Schema::create('approval_log', function (Blueprint $table) {
                $table->id();
                $table->string('modul_approval', 50);
                $table->unsignedBigInteger('id_referensi');
                $table->foreignId('id_approval_flow')->nullable()->constrained('approval_flow_definition')->onDelete('set null');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                // SQLite tidak mendukung enum, jadi kita gunakan string dengan CHECK constraint
                $table->string('status', 20)->default('MENUNGGU');
                $table->text('catatan')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['modul_approval', 'id_referensi']);
            });
            
            // Tambahkan CHECK constraint untuk SQLite
            DB::statement("CREATE TABLE approval_log_final AS 
                SELECT * FROM approval_log_new");
            DB::statement('DROP TABLE approval_log');
            DB::statement("CREATE TABLE approval_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                modul_approval VARCHAR(50) NOT NULL,
                id_referensi INTEGER NOT NULL,
                id_approval_flow INTEGER,
                user_id INTEGER,
                role_id INTEGER NOT NULL,
                status VARCHAR(20) DEFAULT 'MENUNGGU' CHECK (status IN ('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES')),
                catatan TEXT,
                approved_at TIMESTAMP,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                FOREIGN KEY (id_approval_flow) REFERENCES approval_flow_definition(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            )");
            DB::statement('CREATE INDEX approval_log_modul_approval_id_referensi_index ON approval_log(modul_approval, id_referensi)');
            
            // Copy data kembali
            try {
                DB::statement('INSERT INTO approval_log SELECT * FROM approval_log_final');
                DB::statement('DROP TABLE approval_log_final');
                DB::statement('DROP TABLE approval_log_new');
            } catch (\Exception $e) {
                DB::statement('DROP TABLE IF EXISTS approval_log_final');
                DB::statement('DROP TABLE IF EXISTS approval_log_new');
            }
        }
        // Untuk MySQL dan PostgreSQL, migration sebelumnya sudah menangani
    }

    public function down(): void
    {
        // Rollback tidak diperlukan karena ini adalah fix
    }
};
