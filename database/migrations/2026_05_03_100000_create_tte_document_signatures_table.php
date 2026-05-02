<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tte_document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tte_document_seal_id')->constrained('tte_document_seals')->cascadeOnDelete();
            $table->string('signer_role', 32);
            $table->foreignId('expected_pegawai_id')->nullable()->constrained('master_pegawai')->nullOnDelete();
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->char('sign_token', 64)->unique();
            $table->char('signature_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['tte_document_seal_id', 'signer_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tte_document_signatures');
    }
};
