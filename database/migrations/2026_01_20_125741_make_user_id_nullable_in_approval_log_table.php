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
            // SQLite tidak mendukung ALTER COLUMN untuk mengubah nullable
            // Kita perlu membuat tabel baru
            DB::statement('CREATE TABLE approval_log_new AS SELECT * FROM approval_log');
            DB::statement('DROP TABLE approval_log');
            
            Schema::create('approval_log', function (Blueprint $table) {
                $table->id();
                $table->string('modul_approval', 50);
                $table->unsignedBigInteger('id_referensi');
                $table->foreignId('id_approval_flow')->nullable()->constrained('approval_flow_definition')->onDelete('set null');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->enum('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN'])->default('MENUNGGU');
                $table->text('catatan')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['modul_approval', 'id_referensi']);
            });
            
            // Copy data kembali jika ada
            try {
                DB::statement('INSERT INTO approval_log SELECT * FROM approval_log_new');
                DB::statement('DROP TABLE approval_log_new');
            } catch (\Exception $e) {
                // Jika tidak ada data, langsung drop
                DB::statement('DROP TABLE IF EXISTS approval_log_new');
            }
        } else {
            // MySQL/PostgreSQL
            Schema::table('approval_log', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: buat kembali dengan user_id NOT NULL
            DB::statement('CREATE TABLE approval_log_temp AS SELECT * FROM approval_log');
            DB::statement('DROP TABLE approval_log');
            
            Schema::create('approval_log', function (Blueprint $table) {
                $table->id();
                $table->string('modul_approval', 50);
                $table->unsignedBigInteger('id_referensi');
                $table->foreignId('id_approval_flow')->nullable()->constrained('approval_flow_definition')->onDelete('set null');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->enum('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN'])->default('MENUNGGU');
                $table->text('catatan')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['modul_approval', 'id_referensi']);
            });
            
            // Copy data kembali (hanya yang user_id tidak null)
            try {
                DB::statement('INSERT INTO approval_log SELECT * FROM approval_log_temp WHERE user_id IS NOT NULL');
                DB::statement('DROP TABLE approval_log_temp');
            } catch (\Exception $e) {
                DB::statement('DROP TABLE IF EXISTS approval_log_temp');
            }
        } else {
            Schema::table('approval_log', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable(false)->change();
            });
        }
    }
};