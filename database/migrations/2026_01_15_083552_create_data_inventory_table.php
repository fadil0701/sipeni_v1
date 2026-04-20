<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_inventory', function (Blueprint $table) {
            $table->id('id_inventory');
            
            // Relasi utama
            $table->foreignId('id_data_barang')->constrained('master_data_barang', 'id_data_barang')->onDelete('cascade');
            $table->foreignId('id_gudang')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->foreignId('id_anggaran')->constrained('master_sumber_anggaran', 'id_anggaran')->onDelete('cascade');
            $table->foreignId('id_sub_kegiatan')->constrained('master_sub_kegiatan', 'id_sub_kegiatan')->onDelete('cascade');
            
            // Klasifikasi
            $table->enum('jenis_inventory', ['ASET', 'PERSEDIAAN', 'FARMASI']);
            $table->integer('tahun_anggaran');
            
            // Informasi kuantitas & harga
            $table->decimal('qty_input', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            
            // Informasi teknis
            $table->string('merk', 100)->nullable();
            $table->string('tipe', 100)->nullable();
            $table->text('spesifikasi')->nullable();
            $table->integer('tahun_produksi')->nullable();
            
            // Informasi batch / seri (DINAMIS)
            $table->string('no_seri', 100)->nullable(); // NULL jika bukan ASET
            $table->string('no_batch', 100)->nullable(); // NULL jika ASET
            $table->date('tanggal_kedaluwarsa')->nullable(); // NULL jika ASET
            
            // Status & dokumen
            $table->enum('status_inventory', ['DRAFT', 'AKTIF', 'DISTRIBUSI', 'HABIS'])->default('DRAFT');
            $table->string('upload_foto', 255)->nullable(); // Foto barang
            $table->string('upload_dokumen', 255)->nullable(); // BA / Faktur / SP
            $table->text('auto_qr_code')->nullable();
            
            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_inventory');
    }
};
