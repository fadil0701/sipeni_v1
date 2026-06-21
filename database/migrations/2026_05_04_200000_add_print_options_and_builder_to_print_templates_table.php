<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_templates', function (Blueprint $table) {
            $table->string('paper_size', 8)->default('a4')->after('header_preset');
            $table->string('orientation', 12)->default('portrait')->after('paper_size');
            $table->unsignedTinyInteger('print_margin_mm')->default(12)->after('orientation');
            $table->json('builder_blocks')->nullable()->after('body')->comment('Urutan blok HTML untuk mode builder drag-drop');
        });
    }

    public function down(): void
    {
        Schema::table('print_templates', function (Blueprint $table) {
            $table->dropColumn(['paper_size', 'orientation', 'print_margin_mm', 'builder_blocks']);
        });
    }
};
