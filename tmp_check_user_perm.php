<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::with('roles.permissions')->find(1);
if (!$user) { echo "User 1 not found\n"; exit; }

echo "User: {$user->email}\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
$perm = 'transaction.draft-distribusi.index';
$has = App\Helpers\PermissionHelper::canAccess($user, $perm) ? 'YES' : 'NO';
echo "Has {$perm}: {$has}\n";

$rolePerms = $user->roles->flatMap->permissions->pluck('name')->unique()->values();
if (!$rolePerms->contains($perm)) {
    echo "Permission missing in role assignments. Similar perms:\n";
    foreach ($rolePerms->filter(fn($p)=>str_starts_with($p,'transaction.draft-distribusi'))->take(20) as $p) {
        echo "- {$p}\n";
    }
}
