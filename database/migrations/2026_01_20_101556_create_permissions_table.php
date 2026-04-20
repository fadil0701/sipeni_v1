<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'inventory.data-stock.index'
            $table->string('display_name'); // e.g., 'View Data Stock'
            $table->string('module'); // e.g., 'inventory', 'transaction', 'master-manajemen'
            $table->string('group')->nullable(); // e.g., 'inventory', 'transaction.permintaan-barang'
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
