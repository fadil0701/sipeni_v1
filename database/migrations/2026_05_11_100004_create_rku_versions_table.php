<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rku_versions', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_rku')->index();
            $table->integer('version_number');
            $table->json('header_snapshot')->nullable();
            $table->json('details_snapshot')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('change_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_rku')->references('id_rku')->on('rku_header')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['id_rku', 'version_number'], 'unique_rku_version');
            $table->index(['created_at', 'id_rku'], 'idx_version_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rku_versions');
    }
};