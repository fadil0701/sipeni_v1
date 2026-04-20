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
        // Ubah jenis_permintaan dari enum menjadi JSON untuk mendukung multichoice
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite tidak mendukung ALTER COLUMN untuk mengubah tipe
            // Kita perlu membuat tabel baru dengan struktur yang benar
            DB::statement('CREATE TABLE permintaan_barang_temp AS SELECT * FROM permintaan_barang');
            DB::statement('DROP TABLE permintaan_barang');
            
            // Buat ulang tabel dengan jenis_permintaan sebagai TEXT (akan menyimpan JSON)
            Schema::create('permintaan_barang', function (Blueprint $table) {
                $table->id('id_permintaan');
                $table->string('no_permintaan', 50)->unique();
                $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
                $table->foreignId('id_pemohon')->constrained('master_pegawai')->onDelete('cascade');
                $table->date('tanggal_permintaan');
                $table->text('jenis_permintaan')->nullable(); // Simpan sebagai JSON string
                $table->enum('status_permintaan', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK'])->default('DRAFT');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
            
            // Copy data dan convert enum ke JSON array
            $permintaans = DB::table('permintaan_barang_temp')->get();
            foreach ($permintaans as $permintaan) {
                $jenisPermintaanJson = json_encode([$permintaan->jenis_permintaan]);
                DB::table('permintaan_barang')->insert([
                    'id_permintaan' => $permintaan->id_permintaan,
                    'no_permintaan' => $permintaan->no_permintaan,
                    'id_unit_kerja' => $permintaan->id_unit_kerja,
                    'id_pemohon' => $permintaan->id_pemohon,
                    'tanggal_permintaan' => $permintaan->tanggal_permintaan,
                    'jenis_permintaan' => $jenisPermintaanJson,
                    'status_permintaan' => $permintaan->status_permintaan,
                    'keterangan' => $permintaan->keterangan,
                    'created_at' => $permintaan->created_at,
                    'updated_at' => $permintaan->updated_at,
                ]);
            }
            
            DB::statement('DROP TABLE permintaan_barang_temp');
        } else {
            // MySQL/PostgreSQL
            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->json('jenis_permintaan')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum jika rollback
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: perlu membuat tabel baru dengan enum
            DB::statement('CREATE TABLE permintaan_barang_temp AS SELECT * FROM permintaan_barang');
            DB::statement('DROP TABLE permintaan_barang');
            
            Schema::create('permintaan_barang', function (Blueprint $table) {
                $table->id('id_permintaan');
                $table->string('no_permintaan', 50)->unique();
                $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
                $table->foreignId('id_pemohon')->constrained('master_pegawai')->onDelete('cascade');
                $table->date('tanggal_permintaan');
                $table->enum('jenis_permintaan', ['BARANG', 'ASET']);
                $table->enum('status_permintaan', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK'])->default('DRAFT');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
            
            // Copy data dan ambil nilai pertama dari JSON array
            $permintaans = DB::table('permintaan_barang_temp')->get();
            foreach ($permintaans as $permintaan) {
                $jenisPermintaan = json_decode($permintaan->jenis_permintaan, true);
                $jenisPermintaanEnum = is_array($jenisPermintaan) && count($jenisPermintaan) > 0 ? $jenisPermintaan[0] : 'BARANG';
                
                DB::table('permintaan_barang')->insert([
                    'id_permintaan' => $permintaan->id_permintaan,
                    'no_permintaan' => $permintaan->no_permintaan,
                    'id_unit_kerja' => $permintaan->id_unit_kerja,
                    'id_pemohon' => $permintaan->id_pemohon,
                    'tanggal_permintaan' => $permintaan->tanggal_permintaan,
                    'jenis_permintaan' => $jenisPermintaanEnum,
                    'status_permintaan' => $permintaan->status_permintaan,
                    'keterangan' => $permintaan->keterangan,
                    'created_at' => $permintaan->created_at,
                    'updated_at' => $permintaan->updated_at,
                ]);
            }
            
            DB::statement('DROP TABLE permintaan_barang_temp');
        } else {
            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->enum('jenis_permintaan', ['BARANG', 'ASET'])->change();
            });
        }
    }
};
