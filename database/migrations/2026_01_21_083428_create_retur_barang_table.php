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
        Schema::create('retur_barang', function (Blueprint $table) {
            $table->id('id_retur');
            $table->string('no_retur', 50)->unique();
            $table->foreignId('id_penerimaan')->nullable()->constrained('penerimaan_barang', 'id_penerimaan')->onDelete('cascade'); // Retur dari penerimaan
            $table->foreignId('id_distribusi')->nullable()->constrained('transaksi_distribusi', 'id_distribusi')->onDelete('cascade'); // Atau langsung dari distribusi
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade'); // Unit kerja yang melakukan retur
            $table->foreignId('id_gudang_asal')->constrained('master_gudang', 'id_gudang')->onDelete('cascade'); // Gudang unit
            $table->foreignId('id_gudang_tujuan')->constrained('master_gudang', 'id_gudang')->onDelete('cascade'); // Gudang pusat
            $table->foreignId('id_pegawai_pengirim')->constrained('master_pegawai')->onDelete('cascade'); // Pegawai yang mengirim retur
            $table->date('tanggal_retur');
            $table->enum('status_retur', ['DRAFT', 'DIAJUKAN', 'DITERIMA', 'DITOLAK'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->text('alasan_retur')->nullable(); // Alasan melakukan retur
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_barang');
    }
};
