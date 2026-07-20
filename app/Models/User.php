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
 * @property int|null $pegawai_id
 * @property string|null $username
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 * @property string|null $avatar
 * @property string|null $password_changed_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $primary_role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Module> $modules
 * @property-read int|null $modules_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\MasterPegawai|null $pegawai
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $userRoles
 * @property-read int|null $user_roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePegawaiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 * @mixin \Eloquent
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
     * Check if user has access to a specific module.
     */
    public function hasModule(string $moduleName): bool
    {
        return $this->modules()->where('name', $moduleName)->exists();
    }

    /**
     * Cek permission via cache nama (wildcard Spatie) — tanpa hydrate model Permission tiap request.
     */
    public function hasPermission(string $permissionName): bool
    {
        return \App\Helpers\PermissionHelper::ownsPermission($this, $permissionName);
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
