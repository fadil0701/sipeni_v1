<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Tte\TteSealService;

class DocumentVerificationController extends Controller
{
    public function show(string $token, TteSealService $tteSealService)
    {
        $seal = $tteSealService->findByPublicToken($token);

        if (! $seal) {
            abort(404, 'Segel tidak ditemukan atau tautan tidak valid.');
        }

        $seal->load(['signatures.signedBy']);

        return view('public.document-verification', [
            'seal' => $seal,
        ]);
    }
}
