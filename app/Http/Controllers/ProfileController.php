<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Support\Storage\PrivateStorage;
use Illuminate\Support\Facades\Storage;
use App\Services\Auth\TwoFactorService;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load(['pegawai.jabatan', 'pegawai.unitKerja', 'roles']);

        $roles = $user->roles()
            ->withCount([
                'workflowPermissions',
                'workflowPermissions as approve_capability_count' => fn ($q) => $q->where('can_approve', true),
                'workflowPermissions as monitoring_capability_count' => fn ($q) => $q->where(function ($q2): void {
                    $q2->where('can_process', true)->orWhere('can_verify', true);
                }),
            ])
            ->get();
        $roleInfo = $this->getRoleInfo($roles);

        $lastAuditActivity = AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $loginCount = AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['login', 'logged_in'])
            ->count();

        $twoFactorSetup = null;
        if (TwoFactorService::userMustUseTwoFactor($user) && ! TwoFactorService::userHasConfirmedTwoFactor($user)) {
            $secret = TwoFactorService::generateSecret();
            $request->session()->put('two_factor.setup_secret', $secret);
            $otpauthUrl = TwoFactorService::otpAuthUrl($user, $secret);
            $twoFactorSetup = [
                'secret' => $secret,
                'qr_svg' => QrCode::format('svg')->size(200)->margin(1)->generate($otpauthUrl),
            ];
        }

        return view('profile.index', compact('user', 'roleInfo', 'lastAuditActivity', 'loginCount', 'twoFactorSetup'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        $validated = $request->validated();

        $beforeData = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $user->update($validated);

        $this->logAudit(
            $user->id,
            'profile_updated',
            'users',
            $user->id,
            $beforeData,
            $validated,
            $request
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak cocok.']);
        }

        $beforeData = [
            'password_changed_at' => $user->password_changed_at?->toISOString(),
        ];

        $user->update([
            'password' => $request->password,
            'password_changed_at' => now(),
        ]);

        $this->logAudit(
            $user->id,
            'password_changed',
            'users',
            $user->id,
            $beforeData,
            ['password_changed_at' => now()->toISOString()],
            $request
        );

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = $request->user();
        $file = $request->file('avatar');

        $oldAvatar = $user->avatar;

        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('avatars', $filename, 'local');

        $user->update(['avatar' => $path]);

        if ($oldAvatar) {
            PrivateStorage::delete($oldAvatar);
        }

        $this->logAudit(
            $user->id,
            'avatar_updated',
            'users',
            $user->id,
            ['avatar' => $oldAvatar],
            ['avatar' => $path],
            $request
        );

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;

        if ($oldAvatar) {
            PrivateStorage::delete($oldAvatar);
        }

        $user->update(['avatar' => null]);

        $this->logAudit(
            $user->id,
            'avatar_removed',
            'users',
            $user->id,
            ['avatar' => $oldAvatar],
            ['avatar' => null],
            $request
        );

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    protected function getRoleInfo($roles)
    {
        $roleInfo = [
            'roles' => [],
            'level' => null,
            'scope' => null,
            'has_approval' => false,
            'has_monitoring' => false,
        ];

        $roleHierarchy = [
            'SYSTEM' => ['level' => 'L1', 'label' => 'SYSTEM'],
            'STRUKTURAL' => ['level' => 'L2', 'label' => 'STRUKTURAL'],
            'MANAJERIAL' => ['level' => 'L3', 'label' => 'MANAJERIAL'],
            'OPERATOR' => ['level' => 'L4', 'label' => 'OPERATOR'],
            'UNIT KERJA' => ['level' => 'L5', 'label' => 'UNIT'],
        ];

        foreach ($roles as $role) {
            $roleData = [
                'name' => $role->name,
                'display_name' => $role->display_name ?? $role->name,
                'group' => $role->group ?? 'UNIT KERJA',
            ];

            $groupKey = strtoupper($roleData['group']);
            if (isset($roleHierarchy[$groupKey])) {
                $roleData['level'] = $roleHierarchy[$groupKey]['level'];
            }

            $roleData['has_approval'] = ($role->approve_capability_count ?? 0) > 0;
            $roleData['has_monitoring'] = ($role->monitoring_capability_count ?? 0) > 0;

            if ($roleData['has_approval']) {
                $roleInfo['has_approval'] = true;
            }
            if ($roleData['has_monitoring']) {
                $roleInfo['has_monitoring'] = true;
            }

            $roleInfo['roles'][] = $roleData;
        }

        $roleInfo['level'] = collect($roleInfo['roles'])->pluck('level')->filter()->min();

        return $roleInfo;
    }

    protected function logAudit(int $userId, string $action, string $table, int $dataId, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $table,
            'data_id' => $dataId,
            'before_data' => $before ? json_encode($before) : null,
            'after_data' => $after ? json_encode($after) : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function confirmTwoFactor(Request $request)
    {
        $user = $request->user();
        $request->validate(['code' => 'required|string']);

        $secret = $request->session()->get('two_factor.setup_secret');
        if (! is_string($secret) || $secret === '') {
            return back()->with('error', 'Sesi setup 2FA kedaluwarsa. Muat ulang halaman profil.');
        }

        $tempUser = clone $user;
        $tempUser->two_factor_secret = TwoFactorService::encryptSecret($secret);
        if (! TwoFactorService::verifyCode($tempUser, $request->input('code'))) {
            return back()->with('error', 'Kode OTP tidak valid.');
        }

        $recoveryCodes = TwoFactorService::generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => TwoFactorService::encryptSecret($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor.setup_secret');

        return back()->with('success', 'Two-factor authentication aktif. Simpan recovery codes: '.implode(', ', $recoveryCodes));
    }

    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();
        $request->validate(['password' => 'required|string']);

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->with('error', 'Password tidak valid.');
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Two-factor authentication dinonaktifkan.');
    }
}