<?php

namespace App\Http\Controllers;

use App\Services\PanduanPenggunaPdfExporter;
use App\Services\PanduanPenggunaService;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PanduanPenggunaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleGuides = PanduanPenggunaService::roleGuidesForUser($user);
        $chapters = PanduanPenggunaService::generalChapters();
        $pdfAvailable = PanduanPenggunaPdfExporter::isAvailable();

        return view('panduan.index', compact('roleGuides', 'chapters', 'pdfAvailable'));
    }

    public function show(string $doc)
    {
        try {
            $resolved = PanduanPenggunaService::resolveDoc($doc);
            $html = PanduanPenggunaService::htmlFromDoc($doc);
        } catch (RuntimeException) {
            abort(404, 'Panduan tidak ditemukan.');
        }

        $roleGuides = PanduanPenggunaService::roleGuidesForUser(auth()->user());
        $chapters = PanduanPenggunaService::generalChapters();
        $pdfAvailable = PanduanPenggunaPdfExporter::isAvailable();

        return view('panduan.show', [
            'doc' => $doc,
            'title' => $resolved['title'],
            'html' => $html,
            'roleGuides' => $roleGuides,
            'chapters' => $chapters,
            'pdfAvailable' => $pdfAvailable,
        ]);
    }

    public function pdf(string $doc)
    {
        if (! PanduanPenggunaPdfExporter::isAvailable()) {
            abort(Response::HTTP_SERVICE_UNAVAILABLE, 'Ekspor PDF tidak tersedia di server ini.');
        }

        try {
            $resolved = PanduanPenggunaService::resolveDoc($doc);
            $absolute = PanduanPenggunaService::absolutePath($resolved['relative']);
            [$binary, $filename] = PanduanPenggunaPdfExporter::exportMarkdownFile($absolute);
        } catch (RuntimeException) {
            abort(404, 'Panduan tidak ditemukan.');
        }

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
