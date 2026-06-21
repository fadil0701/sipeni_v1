<?php

namespace App\Helpers;

class AuditHelper
{
    public static function getSeverity(string $action): array
    {
        $infoActions = ['login', 'logged_in', 'view', 'show', 'index'];
        $warningActions = ['update', 'edit', 'store', 'create', 'export', 'download'];
        $criticalActions = ['delete', 'destroy', 'force', 'hard_delete', 'terminate', 'cancel'];
        $securityActions = ['password_changed', 'password_updated', 'role_changed', 'permission_changed', 'login_failed', 'lockout', 'unlock'];

        $actionLower = strtolower($action);

        if (in_array($actionLower, $securityActions)) {
            return [
                'severity' => 'SECURITY',
                'color' => 'purple',
                'bg' => 'bg-violet-50',
                'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
            ];
        }

        if (in_array($actionLower, $criticalActions)) {
            return [
                'severity' => 'CRITICAL',
                'color' => 'danger',
                'bg' => 'bg-red-50',
                'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            ];
        }

        if (in_array($actionLower, $warningActions)) {
            return [
                'severity' => 'WARNING',
                'color' => 'warning',
                'bg' => 'bg-amber-50',
                'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            ];
        }

        return [
            'severity' => 'INFO',
            'color' => 'info',
            'bg' => 'bg-blue-50',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ];
    }

    public static function getActivityDescription(string $action, ?string $tableName = null): string
    {
        $descriptions = [
            'login' => 'masuk ke sistem',
            'logged_in' => 'masuk ke sistem',
            'logout' => 'keluar dari sistem',
            'logged_out' => 'keluar dari sistem',
            'create' => 'membuat',
            'store' => 'membuat',
            'update' => 'mengubah',
            'edit' => 'mengubah',
            'delete' => 'menghapus',
            'destroy' => 'menghapus',
            'view' => 'melihat',
            'show' => 'melihat',
            'password_changed' => 'mengubah password',
            'password_updated' => 'mengubah password',
            'profile_updated' => 'memperbarui profil',
            'avatar_updated' => 'mengubah foto profil',
            'role_changed' => 'mengubah role',
            'permission_changed' => 'mengubah permission',
            'approve' => 'menyetuju',
            'verified' => 'memverifikasi',
            'reject' => 'menolak',
            'process' => 'memproses',
            'submit' => 'menyerahkan',
            'export' => 'mengekspor',
            'download' => 'mengunduh',
        ];

        $actionLower = strtolower($action);
        return $descriptions[$actionLower] ?? $action;
    }
}