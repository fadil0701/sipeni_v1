<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rku_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('rku_detail', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('rku_detail', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('rku_detail', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('subtotal_rencana');
            }
            if (!Schema::hasColumn('rku_detail', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('is_approved');
            }
            if (!Schema::hasColumn('rku_detail', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_notes');
            }
            if (!Schema::hasColumn('rku_detail', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('rku_detail', 'last_price')) {
                $table->decimal('last_price', 15, 2)->nullable()->after('approved_at')->comment('Harga tahun sebelumnya');
            }
            if (!Schema::hasColumn('rku_detail', 'price_change_pct')) {
                $table->decimal('price_change_pct', 5, 2)->nullable()->after('last_price')->comment('Persentase perubahan harga');
            }

            try {
                $table->index(['id_rku', 'id_data_barang'], 'idx_rku_detail_rku_barang');
            } catch (\Exception $e) {}
            try {
                $table->index(['id_data_barang', 'id_rku'], 'idx_rku_detail_barang_rku');
            } catch (\Exception $e) {}
        });

        Schema::table('rku_detail', function (Blueprint $table) {
            try {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            } catch (\Exception $e) {}
            try {
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            } catch (\Exception $e) {}
            try {
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            } catch (\Exception $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('rku_detail', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'deleted_at', 'created_by', 'updated_by',
                'is_approved', 'approval_notes', 'approved_by', 'approved_at',
                'last_price', 'price_change_pct'
            ]);
            $table->dropIndex('idx_rku_barang');
            $table->dropIndex('idx_barang_rku');
        });
    }
};