<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('module_key', 64)->nullable()->index();
            $table->string('action', 64);
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_url', 2048)->nullable();
            $table->string('method', 16)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['entity_type', 'entity_id'], 'activity_logs_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
