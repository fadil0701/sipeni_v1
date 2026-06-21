<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 160)->unique()->comment('Identifikator stabil, mis. distribusi.sbbk');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->longText('body')->comment('HTML; placeholder {{kunci}} atau {{nested.key}}');
            $table->json('sample_data')->nullable()->comment('Data contoh untuk pratinjau');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_templates');
    }
};
