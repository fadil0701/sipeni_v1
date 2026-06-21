<?php

namespace App\Support\Http;

/**
 * Pesan error aman untuk ditampilkan ke pengguna (tanpa detail exception internal).
 */
final class SafeUserMessage
{
    public static function operationFailed(string $operation): string
    {
        return 'Terjadi kesalahan saat '.$operation.'. Silakan coba lagi atau hubungi administrator.';
    }
}
