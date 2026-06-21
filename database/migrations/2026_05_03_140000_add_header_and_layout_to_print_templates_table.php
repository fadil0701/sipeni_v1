<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_templates', function (Blueprint $table) {
            $table->longText('header_html')->nullable()->after('description');
            $table->string('layout_mode', 32)->default('full_page')->after('header_html');
            $table->string('header_preset', 64)->nullable()->after('layout_mode');
        });
    }

    public function down(): void
    {
        Schema::table('print_templates', function (Blueprint $table) {
            $table->dropColumn(['header_html', 'layout_mode', 'header_preset']);
        });
    }
};
