<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Get image URL with fallback
     *
     * @param string|null $path
     * @param string|null $fallbackUrl
     * @return string
     */
    public static function getImageUrl(?string $path, ?string $fallbackUrl = null): string
    {
        if (empty($path)) {
            return $fallbackUrl ?? self::getPlaceholderImage();
        }

        // Jika sudah URL lengkap, return langsung
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Cek apakah file ada di storage
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return $fallbackUrl ?? self::getPlaceholderImage();
    }

    /**
     * Get placeholder image SVG
     *
     * @return string
     */
    public static function getPlaceholderImage(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="#f3f4f6"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#9ca3af" font-family="sans-serif" font-size="14">Foto tidak ditemukan</text></svg>';
        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    /**
     * Check if image exists
     *
     * @param string|null $path
     * @return bool
     */
    public static function imageExists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }

        return Storage::disk('public')->exists($path);
    }
}
