<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterGudangSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MasterGudangPusatSeeder::class,
            MasterGudangUnitSeeder::class,
        ]);
    }
}

