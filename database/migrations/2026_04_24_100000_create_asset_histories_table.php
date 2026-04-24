<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_register_aset');
            $table->string('event', 100);
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('id_register_aset');
            $table->index('event');
            $table->foreign('id_register_aset')->references('id_register_aset')->on('register_aset')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
