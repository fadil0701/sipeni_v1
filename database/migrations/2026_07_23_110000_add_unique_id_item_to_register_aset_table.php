<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('register_aset') || ! Schema::hasColumn('register_aset', 'id_item')) {
            return;
        }

        // Hapus duplikat id_item (pertahankan register dengan id terkecil).
        $duplicates = DB::table('register_aset')
            ->select('id_item', DB::raw('MIN(id_register_aset) as keep_id'))
            ->whereNotNull('id_item')
            ->groupBy('id_item')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('register_aset')
                ->where('id_item', $dup->id_item)
                ->where('id_register_aset', '!=', $dup->keep_id)
                ->delete();
        }

        $alreadyUnique = collect(Schema::getIndexes('register_aset'))
            ->contains(fn (array $index) => ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['id_item']);

        if ($alreadyUnique) {
            return;
        }

        Schema::table('register_aset', function (Blueprint $table) {
            $table->unique('id_item', 'register_aset_id_item_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('register_aset') || ! Schema::hasColumn('register_aset', 'id_item')) {
            return;
        }

        $hasNamed = collect(Schema::getIndexes('register_aset'))
            ->contains(fn (array $index) => ($index['name'] ?? '') === 'register_aset_id_item_unique');

        if (! $hasNamed) {
            return;
        }

        Schema::table('register_aset', function (Blueprint $table) {
            $table->dropUnique('register_aset_id_item_unique');
        });
    }
};
