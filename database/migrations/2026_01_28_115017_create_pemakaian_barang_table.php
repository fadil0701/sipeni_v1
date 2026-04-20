<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemakaian_barang', function (Blueprint $table) {
            $table->id('id_pemakaian');
            $table->string('no_pemakaian', 50)->unique();
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->foreignId('id_gudang')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->foreignId('id_pegawai_pemakai')->constrained('master_pegawai')->onDelete('cascade');
            $table->date('tanggal_pemakaian');
            $table->enum('status_pemakaian', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->text('alasan_pemakaian')->nullable();
            $table->foreignId('id_approver')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('tanggal_approval')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_pemakaian_barang', function (Blueprint $table) {
            $table->id('id_detail_pemakaian');
            $table->foreignId('id_pemakaian')->constrained('pemakaian_barang', 'id_pemakaian')->onDelete('cascade');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->decimal('qty_pemakaian', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->text('alasan_pemakaian_item')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pemakaian_barang');
        Schema::dropIfExists('pemakaian_barang');
    }
};
