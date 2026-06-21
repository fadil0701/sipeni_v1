<?php

namespace App\Services;

use App\Helpers\PermissionHelper;
use App\Models\ApprovalLog;
use App\Models\MasterPegawai;
use App\Models\PenerimaanBarang;
use App\Models\PermintaanBarang;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ApprovalActionRequired;
use App\Notifications\PenerimaanAwaitingVerification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

final class AppNotificationService
{
    public static function notifyApprovalPending(ApprovalLog $approvalLog): void
    {
        $approvalLog->loadMissing(['approvalFlow.role']);
        $roleId = $approvalLog->role_id;
        if (! $roleId) {
            return;
        }

        $permintaan = PermintaanBarang::query()->find($approvalLog->id_referensi);
        $permintaanNo = $permintaan?->no_permintaan ?? ('#'.$approvalLog->id_referensi);

        $recipients = self::usersForRoleId((int) $roleId);
        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ApprovalActionRequired($approvalLog, $permintaanNo)
        );
    }

    public static function notifyPenerimaanAwaitingVerification(PenerimaanBarang $penerimaan): void
    {
        $penerimaan->loadMissing('unitKerja');
        $unitId = (int) ($penerimaan->id_unit_kerja ?? 0);

        $recipients = User::query()
            ->where('is_active', true)
            ->whereHas('pegawai', fn ($q) => $q->where('id_unit_kerja', $unitId))
            ->get()
            ->filter(fn (User $user) => PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.index')
                || PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.show'));

        if ($recipients->isEmpty()) {
            $recipients = self::usersForPermission('transaction.penerimaan-barang.index');
        }

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new PenerimaanAwaitingVerification($penerimaan));
    }

    /**
     * @return Collection<int, User>
     */
    private static function usersForRoleId(int $roleId): Collection
    {
        $role = Role::query()->find($roleId);
        if (! $role) {
            return collect();
        }

        return User::query()
            ->where('is_active', true)
            ->role($role->name)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private static function usersForPermission(string $permission): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $user) => PermissionHelper::canAccess($user, $permission));
    }
}
