<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rku_header', function (Blueprint $table) {
            if (! Schema::hasColumn('rku_header', 'deleted_at')) {
                $table->softDeletes();
            }

            if (! Schema::hasColumn('rku_header', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable();
            }

            if (! Schema::hasColumn('rku_header', 'version')) {
                $table->integer('version')->default(1);
            }
            if (! Schema::hasColumn('rku_header', 'locked_at')) {
                $table->timestamp('locked_at')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'is_locked')) {
                $table->boolean('is_locked')->default(false);
            }
            if (! Schema::hasColumn('rku_header', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'id_rekening_belanja')) {
                $table->unsignedBigInteger('id_rekening_belanja')->nullable();
            }
            if (! Schema::hasColumn('rku_header', 'priority')) {
                $table->enum('priority', ['normal', 'urgent', 'cito'])->default('normal');
            }
        });

        // Add foreign keys with error handling
        try {
            Schema::table('rku_header', function (Blueprint $table) {
                try {
                    $table->foreign('created_by', 'rku_header_created_by_foreign')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {}
                try {
                    $table->foreign('updated_by', 'rku_header_updated_by_foreign')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {}
                try {
                    $table->foreign('deleted_by', 'rku_header_deleted_by_foreign')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {}
                try {
                    $table->foreign('locked_by', 'rku_header_locked_by_foreign')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {}
                try {
                    $table->foreign('id_rekening_belanja', 'rku_header_rekening_foreign')->references('id')->on('master_rekening_belanja')->onDelete('set null');
                } catch (\Exception $e) {}
            });
        } catch (\Exception $e) {}

        // Add indexes with error handling
        try {
            Schema::table('rku_header', function (Blueprint $table) {
                try {
                    $table->index(['status_rku', 'tahun_anggaran'], 'idx_rku_status_tahun');
                } catch (\Exception $e) {}
                try {
                    $table->index(['id_unit_kerja', 'status_rku'], 'idx_rku_unit_status');
                } catch (\Exception $e) {}
                try {
                    $table->index(['tahun_anggaran', 'id_unit_kerja'], 'idx_rku_tahun_unit');
                } catch (\Exception $e) {}
                try {
                    $table->index(['deleted_at', 'status_rku'], 'idx_rku_deleted_status');
                } catch (\Exception $e) {}
                try {
                    $table->index(['created_at', 'status_rku'], 'idx_rku_created_status');
                } catch (\Exception $e) {}
            });
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        // No down - this is enhancement only
    }
};