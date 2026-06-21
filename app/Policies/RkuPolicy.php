<?php

namespace App\Policies;

use App\Models\RkuHeader;
use App\Models\User;

class RkuPolicy
{
    /**
     * Determine whether the user can view any RKU.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('planning.rku.index')
            || $user->hasPermission('planning.rku.view_all');
    }

    /**
     * Determine whether the user can view the RKU.
     */
    public function view(User $user, RkuHeader $rku): bool
    {
        if ($user->hasPermission('planning.rku.view_all')) {
            return true;
        }

        // Check unit scope
        $userUnitId = $user->pegawai?->id_unit_kerja;
        
        // If user has global view permission, allow
        if ($user->hasPermission('planning.rku.index')) {
            // Check if same unit or has access to all units
            return $rku->id_unit_kerja === $userUnitId 
                || $user->hasPermission('planning.rku.view_all');
        }

        return false;
    }

    /**
     * Determine whether the user can create RKU.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('planning.rku.create');
    }

    /**
     * Determine whether the user can update the RKU.
     */
    public function update(User $user, RkuHeader $rku): bool
    {
        if ($user->hasPermission('planning.rku.update_all')) {
            return true;
        }

        // Check if RKU is editable (draft or rejected, not locked)
        if ($rku->is_locked) {
            return false;
        }

        if (! in_array($rku->status_rku, [
            RkuHeader::STATUS_DRAFT,
            RkuHeader::STATUS_DITOLAK,
            RkuHeader::STATUS_REVISION_REQUIRED,
        ], true)) {
            return false;
        }

        // Check unit scope
        $userUnitId = $user->pegawai?->id_unit_kerja;
        
        return $rku->id_unit_kerja === $userUnitId
            && $user->hasPermission('planning.rku.update');
    }

    /**
     * Determine whether the user can delete the RKU.
     */
    public function delete(User $user, RkuHeader $rku): bool
    {
        if ($user->hasPermission('planning.rku.delete_all')) {
            return true;
        }

        // Can only delete draft RKU
        if ($rku->status_rku !== RkuHeader::STATUS_DRAFT) {
            return false;
        }

        // Check if locked
        if ($rku->is_locked) {
            return false;
        }

        // Check unit scope
        $userUnitId = $user->pegawai?->id_unit_kerja;
        
        return $rku->id_unit_kerja === $userUnitId
            && $user->hasPermission('planning.rku.delete');
    }

    /**
     * Determine whether the user can submit RKU for approval.
     */
    public function submit(User $user, RkuHeader $rku): bool
    {
        if ($rku->status_rku !== RkuHeader::STATUS_DRAFT) {
            return false;
        }

        if ($rku->is_locked) {
            return false;
        }

        // Must have details
        if (!$rku->rkuDetail()->exists()) {
            return false;
        }

        return $user->hasPermission('planning.rku.submit')
            && ($user->hasPermission('planning.rku.submit_all') 
                || $user->pegawai?->id_unit_kerja === $rku->id_unit_kerja);
    }

    /**
     * Determine whether the user can approve RKU.
     */
    public function approve(User $user, RkuHeader $rku): bool
    {
        $reviewStatuses = [
            RkuHeader::STATUS_DIAJUKAN,
            RkuHeader::STATUS_DIPROSES,
            RkuHeader::STATUS_REVIEW_KASUBAG_TU,
            RkuHeader::STATUS_REVIEW_KEPALA_PUSAT,
        ];

        if (! in_array($rku->status_rku, $reviewStatuses, true)) {
            return false;
        }

        if (! $user->hasPermission('planning.rku.approve')) {
            return false;
        }

        if ($user->hasPermission('planning.rku.approve_all')) {
            return true;
        }

        if (in_array($rku->status_rku, [
            RkuHeader::STATUS_REVIEW_KASUBAG_TU,
            RkuHeader::STATUS_REVIEW_KEPALA_PUSAT,
            RkuHeader::STATUS_DIPROSES,
        ], true)) {
            return true;
        }

        return $rku->id_unit_kerja === $user->pegawai?->id_unit_kerja;
    }

    /**
     * Determine whether the user can reject RKU.
     */
    public function reject(User $user, RkuHeader $rku): bool
    {
        return $this->approve($user, $rku);
    }

    /**
     * Determine whether the user can cancel submitted RKU.
     */
    public function cancel(User $user, RkuHeader $rku): bool
    {
        if ($rku->status_rku !== RkuHeader::STATUS_DIAJUKAN) {
            return false;
        }

        // Check if already approved
        if ($rku->status_rku === RkuHeader::STATUS_DISETUJUI) {
            return false;
        }

        return $user->hasPermission('planning.rku.cancel')
            && ($user->hasPermission('planning.rku.cancel_all')
                || $user->pegawai?->id_unit_kerja === $rku->id_unit_kerja);
    }

    /**
     * Determine whether the user can lock/unlock RKU.
     */
    public function lock(User $user, RkuHeader $rku): bool
    {
        return $user->hasPermission('planning.rku.lock')
            || $user->hasPermission('planning.rku.manage');
    }

    /**
     * Determine whether the user can view rekap.
     */
    public function viewRekap(User $user): bool
    {
        return $user->hasPermission('planning.rku.rekap')
            || $user->hasPermission('planning.rku.view_all');
    }

    /**
     * Determine whether the user can export RKU.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('planning.rku.export')
            || $user->hasPermission('planning.rku.view_all');
    }
}