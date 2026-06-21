<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Serve private uploaded files (avatar, dokumen sensitif) — hanya untuk user terautentikasi.
     */
    public function show(Request $request, string $path): StreamedResponse
    {
        $path = ltrim(str_replace(['..', '\\'], '', $path), '/');

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // Avatar: boleh diakses user login manapun yang autentik (untuk tampilan UI).
        // Path lain: bisa diperketat per modul di iterasi berikutnya.
        return Storage::disk('local')->response($path);
    }
}
