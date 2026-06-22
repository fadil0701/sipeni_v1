<?php

namespace App\Support\Http;

use Illuminate\Database\QueryException;
use Throwable;

/**
 * Pesan error aman untuk ditampilkan ke pengguna (tanpa detail exception internal).
 */
final class SafeUserMessage
{
    public static function operationFailed(string $operation): string
    {
        return 'Terjadi kesalahan saat '.$operation.'. Silakan coba lagi atau hubungi administrator.';
    }

    public static function fromThrowable(Throwable $e, string $operation): string
    {
        if ($e instanceof QueryException) {
            return self::fromQueryException($e);
        }

        return self::operationFailed($operation);
    }

    private static function fromQueryException(QueryException $e): string
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        $message = $e->getMessage();

        if (($sqlState === '23000' && $driverCode === 1048) || str_contains($message, 'cannot be null')) {
            if (preg_match("/Column '([^']+)' cannot be null/", $message, $matches)) {
                $label = self::fieldLabel($matches[1]);

                return "Data belum lengkap: {$label} wajib diisi.";
            }

            return 'Data belum lengkap. Periksa kembali isian formulir.';
        }

        if (($sqlState === '23000' && $driverCode === 1062) || str_contains($message, 'Duplicate entry')) {
            return 'Data sudah terdaftar. Periksa NIP, email, atau field unik lain yang sama.';
        }

        if ($sqlState === '23000' && in_array($driverCode, [1451, 1452], true)) {
            return 'Data tidak dapat disimpan karena masih terhubung dengan data lain.';
        }

        return 'Data tidak dapat disimpan. Periksa kembali isian formulir atau hubungi administrator.';
    }

    private static function fieldLabel(string $column): string
    {
        return match ($column) {
            'email_pegawai' => 'email pegawai',
            'nip_pegawai', 'nip' => 'NIP',
            'nama_pegawai', 'nama' => 'nama pegawai',
            'id_unit_kerja', 'unit_kerja_id' => 'unit kerja',
            'id_jabatan' => 'jabatan',
            default => str_replace('_', ' ', $column),
        };
    }
}
