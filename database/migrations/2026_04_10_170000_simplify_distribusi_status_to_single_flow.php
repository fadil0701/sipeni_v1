<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transaksi_distribusi')) {
            return;
        }

        Schema::table('transaksi_distribusi', function (Blueprint $table) {
            $table->string('status_distribusi', 20)->default('draft')->change();
        });

        DB::table('transaksi_distribusi')->where('status_distribusi', 'DRAFT')->update(['status_distribusi' => 'draft']);
        DB::table('transaksi_distribusi')->where('status_distribusi', 'DIKIRIM')->update(['status_distribusi' => 'dikirim']);
        DB::table('transaksi_distribusi')->where('status_distribusi', 'SELESAI')->update(['status_distribusi' => 'selesai']);
        DB::table('transaksi_distribusi')->whereNotIn('status_distribusi', ['draft', 'diproses', 'dikirim', 'selesai'])->update(['status_distribusi' => 'draft']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('transaksi_distribusi')) {
            return;
        }

        DB::table('transaksi_distribusi')->where('status_distribusi', 'draft')->update(['status_distribusi' => 'DRAFT']);
        DB::table('transaksi_distribusi')->where('status_distribusi', 'diproses')->update(['status_distribusi' => 'DRAFT']);
        DB::table('transaksi_distribusi')->where('status_distribusi', 'dikirim')->update(['status_distribusi' => 'DIKIRIM']);
        DB::table('transaksi_distribusi')->where('status_distribusi', 'selesai')->update(['status_distribusi' => 'SELESAI']);
    }
};
