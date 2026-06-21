<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Rbac\RbacRoles;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRoleModel;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Module> $modules
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pegawai_id',
        'username',
        'name',
        'email',
        'password',
        'is_active',
        'last_login',
    ];

    /**
     * Get the pegawai associated with this user
     */
    public function pegawai()
    {
        return $this->hasOne(MasterPegawai::class, 'user_id', 'id');
    }

    public function userRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot('unit_kerja_id', 'created_at');
    }

    /**
     * The modules that belong to the user
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'user_modules', 'user_id', 'module', 'id', 'name')
            ->withTimestamps();
    }

    /**
     * Check if user has access to a specific module
     */
    public function hasModule(string $moduleName): bool
    {
        if (! $this->relationLoaded('modules')) {
            $this->load('modules');
        }

        return $this->modules->contains('name', $moduleName);
    }

    /**
     * Cek permission via Spatie (database + wildcard native bila diaktifkan di config).
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->checkPermissionTo($permissionName);
    }

    /**
     * Assign role memakai nama kanonik (legacy otomatis dinormalisasi).
     *
     * @param  list<string>  $roleNames
     */
    public function assignCanonicalRoles(array $roleNames): void
    {
        $names = RbacRoles::normalizeRoleNames($roleNames);
        $ids = Role::query()->whereIn('name', $names)->where('guard_name', 'web')->pluck('id')->all();
        if ($ids !== []) {
            $this->syncRoles($ids);
        }
    }

    /**
     * @param  list<string>  $roleNames
     */
    public function syncCanonicalRoles(array $roleNames, ?int $unitKerjaId = null): void
    {
        $names = RbacRoles::normalizeRoleNames($roleNames);
        $ids = Role::query()->whereIn('name', $names)->where('guard_name', 'web')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($ids !== []) {
            $this->syncUnifiedRoles($ids, $unitKerjaId);
        }
    }

    /**
     * Get the primary role (first role)
     */
    public function getPrimaryRoleAttribute()
    {
        return $this->roles()->first();
    }

    /**
     * Sinkronisasi role Spatie + tabel user_roles (enterprise scope map).
     *
     * @param  array<int, int>  $roleIds
     */
    public function syncUnifiedRoles(array $roleIds, ?int $unitKerjaId = null): void
    {
        $this->syncRoles($roleIds);

        DB::table('user_roles')->where('user_id', $this->id)->delete();
        foreach ($roleIds as $roleId) {
            if (! SpatieRoleModel::query()->where('id', $roleId)->exists()) {
                continue;
            }
            DB::table('user_roles')->insert([
                'user_id' => $this->id,
                'role_id' => $roleId,
                'unit_kerja_id' => $unitKerjaId,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
