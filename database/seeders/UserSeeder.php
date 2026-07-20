<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Alias praktis: `php artisan db:seed --class=UserSeeder`
 * menjalankan DemoUserSeeder.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoUserSeeder::class);
    }
}
