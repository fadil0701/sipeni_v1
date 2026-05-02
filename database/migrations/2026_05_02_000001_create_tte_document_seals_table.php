<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tte_document_seals', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 64);
            $table->unsignedBigInteger('reference_id');
            $table->char('content_hash_sha256', 64);
            $table->char('public_token', 64)->unique();
            $table->string('verification_code', 32)->unique();
            $table->json('meta')->nullable();
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();

            $table->unique(['document_type', 'content_hash_sha256']);
            $table->index(['document_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tte_document_seals');
    }
};
