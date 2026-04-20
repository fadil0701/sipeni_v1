<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan:
     * 1. Membuat atau memperbarui user administrator
     * 2. Memberikan role 'admin' kepada user tersebut
     */
    public function run(): void
    {
        $this->command->info('Membuat user administrator...');
        
        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('✗ Role admin tidak ditemukan! Silakan jalankan RoleSeeder terlebih dahulu.');
            return;
        }

        // Create or update admin user
        $admin = User::firstOrCreate(
            ['email' => 'pusdatinppkp@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin@123'),
            ]
        );

        // Assign admin role if not already assigned
        if (!$admin->roles->contains($adminRole->id)) {
            $admin->roles()->attach($adminRole->id);
            $this->command->info('✓ Role admin berhasil diberikan kepada user: ' . $admin->email);
        } else {
            $this->command->info('✓ User admin sudah memiliki role admin.');
        }

        $this->command->info("\n✓ User administrator berhasil dibuat/diperbarui!");
        $this->command->info("\n📋 Informasi Login Administrator:");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("Email    : pusdatinppkp@gmail.com");
        $this->command->info("Password : Admin@123");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
}
