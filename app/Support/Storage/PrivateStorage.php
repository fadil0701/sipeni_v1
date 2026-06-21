<?php

namespace App\Support\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Upload sensitif ke disk private (`local` = storage/app/private).
 */
final class PrivateStorage
{
    public const DISK = 'local';

    public static function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, self::DISK);
    }

    public static function storeAs(string $directory, string $filename, UploadedFile $file): string
    {
        return $file->storeAs($directory, $filename, self::DISK);
    }

    public static function put(string $path, string $contents): void
    {
        Storage::disk(self::DISK)->put($path, $contents);
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        } elseif (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function exists(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path)
            || Storage::disk('public')->exists($path);
    }

    public static function isPrivatePath(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path);
    }
}
