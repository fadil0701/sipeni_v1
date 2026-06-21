<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rku_audit_logs', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_rku')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action', 50)->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_rku')->references('id_rku')->on('rku_header')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['created_at', 'action'], 'idx_audit_created_action');
            $table->index(['user_id', 'created_at'], 'idx_audit_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rku_audit_logs');
    }
};