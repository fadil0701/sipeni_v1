<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignAdminRole extends Command
{
    protected $signature = 'user:assign-admin {email? : Email of the user}';

    protected $description = 'Assign super_administrator role to a user';

    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('Enter user email');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        $adminRole = Role::where('name', 'super_administrator')->first();

        if (!$adminRole) {
            $this->error('super_administrator role not found! Please run RoleSeeder first.');
            return 1;
        }

        if ($user->hasRole($adminRole)) {
            $this->info("User {$user->email} already has super_administrator role.");
            return 0;
        }

        $user->assignRole($adminRole);
        $this->info("super_administrator role assigned to user: {$user->email}");

        return 0;
    }
}
