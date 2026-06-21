<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('jabatan_role');
    }

    public function down(): void
    {
        // Pivot tidak dipulihkan; role per user diatur lewat master pegawai / role_user.
    }
};
