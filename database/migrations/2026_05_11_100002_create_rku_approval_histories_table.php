<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rku_approval_histories', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_rku')->index();
            $table->unsignedBigInteger('approver_id')->index();
            $table->string('from_status', 20);
            $table->string('to_status', 20);
            $table->text('notes')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_rku')->references('id_rku')->on('rku_header')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rku_approval_histories');
    }
};