<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;

final class AuditDataSanitizer
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'remember_token',
        'token',
        'secret',
        'api_key',
        'api_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @param  array<string, mixed>|Model|null  $data
     * @return array<string, mixed>|null
     */
    public static function sanitizeAuditData(array|Model|null $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $array = $data instanceof Model ? $data->attributesToArray() : $data;

        return self::sanitizeArray($array);
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public static function diffChanged(array $old, array $new): array
    {
        $old = self::sanitizeArray($old);
        $new = self::sanitizeArray($new);

        $oldOut = [];
        $newOut = [];

        foreach ($new as $key => $value) {
            $previous = $old[$key] ?? null;
            if ($previous !== $value) {
                $oldOut[$key] = $previous;
                $newOut[$key] = $value;
            }
        }

        return [$oldOut, $newOut];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function sanitizeArray(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if (self::isSensitiveKey((string) $key)) {
                $out[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $out[$key] = self::sanitizeArray($value);

                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }

    public static function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if ($lower === $sensitive || str_contains($lower, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
