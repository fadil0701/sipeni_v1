<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

final class SipeniPassword
{
    public const MIN_LENGTH = 12;

    public static function configureDefaults(): Password
    {
        return Password::min(self::MIN_LENGTH)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();
    }

    public static function rule(): Password
    {
        return Password::defaults();
    }

    /**
     * @return list<mixed>
     */
    public static function requiredConfirmed(): array
    {
        return ['required', 'string', 'confirmed', self::rule()];
    }

    /**
     * @return list<mixed>
     */
    public static function optionalConfirmed(): array
    {
        return ['nullable', 'string', 'confirmed', self::rule()];
    }

    public static function requirementHint(): string
    {
        return 'Minimal 12 karakter, mengandung huruf besar, angka, dan simbol.';
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(string $attributeKey = 'password'): array
    {
        return [
            "{$attributeKey}.required" => 'Password wajib diisi.',
            "{$attributeKey}.confirmed" => 'Konfirmasi password tidak cocok.',
            "{$attributeKey}.min" => 'Password minimal 12 karakter.',
            "{$attributeKey}.letters" => 'Password harus mengandung huruf.',
            "{$attributeKey}.mixed" => 'Password harus mengandung huruf besar dan kecil.',
            "{$attributeKey}.mixedCase" => 'Password harus mengandung huruf besar dan kecil.',
            "{$attributeKey}.numbers" => 'Password harus mengandung angka.',
            "{$attributeKey}.symbols" => 'Password harus mengandung simbol.',
        ];
    }
}
