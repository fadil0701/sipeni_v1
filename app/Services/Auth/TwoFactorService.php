<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * TOTP two-factor authentication (RFC 6238) tanpa dependency eksternal.
 */
final class TwoFactorService
{
    public static function isEnabled(): bool
    {
        return (bool) config('sipeni.two_factor.enabled', true);
    }

    public static function userMustUseTwoFactor(User $user): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        $requiredRoles = config('sipeni.two_factor.required_roles', ['super_administrator']);

        return $user->hasAnyRole($requiredRoles);
    }

    public static function userHasConfirmedTwoFactor(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null && ! empty($user->two_factor_secret);
    }

    public static function userNeedsChallenge(User $user): bool
    {
        return self::userMustUseTwoFactor($user) && self::userHasConfirmedTwoFactor($user);
    }

    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    /**
     * @return list<string>
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }

    public static function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public static function decryptSecret(?string $encrypted): ?string
    {
        if (empty($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function verifyCode(User $user, string $code): bool
    {
        $secret = self::decryptSecret($user->two_factor_secret);
        if (! $secret) {
            return false;
        }

        $code = preg_replace('/\s+/', '', $code) ?? $code;
        if (! preg_match('/^\d{6}$/', $code)) {
            return self::verifyRecoveryCode($user, $code);
        }

        $timeSlice = (int) floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            if (hash_equals(self::totp($secret, $timeSlice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function verifyRecoveryCode(User $user, string $code): bool
    {
        if (empty($user->two_factor_recovery_codes)) {
            return false;
        }

        try {
            $codes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return false;
        }

        if (! is_array($codes)) {
            return false;
        }

        $normalized = Str::upper(str_replace(' ', '', $code));
        foreach ($codes as $index => $stored) {
            if (hash_equals(Str::upper((string) $stored), $normalized)) {
                unset($codes[$index]);
                $user->forceFill([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($codes))),
                ])->save();

                return true;
            }
        }

        return false;
    }

    public static function otpAuthUrl(User $user, string $secret): string
    {
        $issuer = rawurlencode((string) config('app.name', 'SI-MANTIK'));
        $label = rawurlencode($issuer.':'.$user->email);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&digits=6&period=30";
    }

    private static function totp(string $secret, int $timeSlice): string
    {
        $key = self::base32Decode($secret);
        $time = pack('N*', 0, $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $chunks = str_split($binary, 5);
        $encoded = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }

    private static function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? $secret);
        $binary = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = str_split($binary, 8);
        $decoded = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) === 8) {
                $decoded .= chr(bindec($byte));
            }
        }

        return $decoded;
    }
}
