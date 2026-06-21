<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintTemplate;
use App\Services\PrintTemplatePdfExporter;
use App\Services\PrintTemplateRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrintTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $q = PrintTemplate::query()->orderBy('name');
        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', '%'.$s.'%')
                    ->orWhere('key', 'like', '%'.$s.'%');
            });
        }

        $templates = $q->paginate(15)->appends($request->query());

        $summary = [
            'total_templates' => PrintTemplate::count(),
            'active_templates' => PrintTemplate::query()->where('is_active', true)->count(),
        ];

        return view('admin.print-templates.index', compact('templates', 'summary'));
    }

    public function create(): View
    {
        return view('admin.print-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        PrintTemplate::create($this->validatedFromRequest($request));

        return redirect()->route('admin.print-templates.index')
            ->with('success', 'Template cetak berhasil dibuat.');
    }

    public function show(PrintTemplate $printTemplate): View
    {
        $fromHtml = PrintTemplateRenderer::extractPlaceholderGroups(
            PrintTemplateRenderer::mergedTemplateString($printTemplate)
        );
        $sample = is_array($printTemplate->sample_data) ? $printTemplate->sample_data : null;
        $placeholderGroups = PrintTemplateRenderer::mergePlaceholderGroupsWithData(
            $fromHtml,
            $sample,
            (string) $printTemplate->key
        );

        return view('admin.print-templates.show', compact('printTemplate', 'placeholderGroups'));
    }

    public function edit(PrintTemplate $printTemplate): View
    {
        return view('admin.print-templates.edit', compact('printTemplate'));
    }

    public function update(Request $request, PrintTemplate $printTemplate): RedirectResponse
    {
        $printTemplate->update($this->validatedFromRequest($request, $printTemplate->id));

        return redirect()->route('admin.print-templates.index')
            ->with('success', 'Template cetak berhasil diperbarui.');
    }

    public function destroy(PrintTemplate $printTemplate): RedirectResponse
    {
        $printTemplate->delete();

        return redirect()->route('admin.print-templates.index')
            ->with('success', 'Template cetak dihapus.');
    }

    /**
     * Pratinjau HTML hasil render (data = sample_data dari template).
     */
    public function preview(PrintTemplate $printTemplate): Response
    {
        $data = is_array($printTemplate->sample_data) ? $printTemplate->sample_data : [];
        $html = PrintTemplateRenderer::render($printTemplate, $data);

        return response()
            ->view('admin.print-templates.preview-frame', [
                'title' => 'Pratinjau: '.$printTemplate->name,
                'html' => $html,
                'printTemplate' => $printTemplate,
                'allowPdfExport' => true,
            ])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Ekspor PDF (data contoh) — memerlukan dompdf/dompdf terpasang.
     */
    public function pdf(PrintTemplate $printTemplate): Response|RedirectResponse
    {
        if (! PrintTemplatePdfExporter::isAvailable()) {
            return redirect()
                ->route('admin.print-templates.edit', $printTemplate)
                ->with('error', 'Ekspor PDF membutuhkan dependensi dompdf. Jalankan: composer update');
        }

        $data = is_array($printTemplate->sample_data) ? $printTemplate->sample_data : [];
        $inner = PrintTemplateRenderer::render($printTemplate, $data);
        $full = PrintTemplatePdfExporter::wrapHtmlForPdf($inner, $printTemplate);

        try {
            [$binary, $filename] = PrintTemplatePdfExporter::renderBinary($printTemplate, $full);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.print-templates.edit', $printTemplate)
                ->with('error', 'Gagal membuat PDF: '.$e->getMessage());
        }

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function validatedFromRequest(Request $request, ?int $ignoreId = null): array
    {
        $keyRule = ['required', 'string', 'max:160', 'regex:/^[a-z][a-z0-9._-]*$/'];
        $keyRule[] = Rule::unique('print_templates', 'key')->ignore($ignoreId);

        $validated = $request->validate([
            'key' => $keyRule,
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'header_html' => ['nullable', 'string', 'max:500000'],
            'layout_mode' => ['required', 'string', 'in:full_page,fragment'],
            'header_preset' => ['nullable', 'string', 'max:64'],
            'paper_size' => ['required', 'string', 'in:a4,f4'],
            'orientation' => ['required', 'string', 'in:portrait,landscape'],
            'print_margin_mm' => ['required', 'integer', 'min:5', 'max:30'],
            'body' => ['nullable', 'string', 'max:500000'],
            'builder_blocks_json' => ['nullable', 'string', 'max:2000000'],
            'sample_data' => ['nullable', 'string', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['body'] = (string) ($validated['body'] ?? '');

        $sampleRaw = $validated['sample_data'] ?? null;
        unset($validated['sample_data']);

        if ($sampleRaw !== null && trim((string) $sampleRaw) !== '') {
            $decoded = json_decode((string) $sampleRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'sample_data' => 'Format JSON tidak valid: '.json_last_error_msg(),
                ]);
            }
            $validated['sample_data'] = $decoded;
        } else {
            $validated['sample_data'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $validated = $this->applyBuilderBlocks($request, $validated);

        unset($validated['builder_blocks_json']);

        if (trim($validated['body']) === '') {
            throw ValidationException::withMessages([
                'body' => 'Isi dokumen tidak boleh kosong.',
            ]);
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyBuilderBlocks(Request $request, array $validated): array
    {
        if (! $request->boolean('use_block_builder')) {
            $validated['builder_blocks'] = null;

            return $validated;
        }

        $raw = $request->input('builder_blocks_json', '[]');
        $blocks = is_string($raw) ? json_decode($raw, true) : null;
        if (! is_array($blocks)) {
            throw ValidationException::withMessages([
                'builder_blocks_json' => 'Format blok builder tidak valid (JSON).',
            ]);
        }

        $normalized = [];
        foreach ($blocks as $b) {
            if (! is_array($b)) {
                continue;
            }
            $html = (string) ($b['html'] ?? '');
            $id = trim((string) ($b['id'] ?? ''));
            if ($id === '') {
                $id = (string) Str::uuid();
            }
            $normalized[] = ['id' => $id, 'html' => $html];
        }

        if ($normalized === []) {
            $validated['builder_blocks'] = null;

            return $validated;
        }

        $parts = [];
        foreach ($normalized as $row) {
            if (trim((string) ($row['html'] ?? '')) !== '') {
                $parts[] = (string) $row['html'];
            }
        }
        if ($parts !== []) {
            $validated['body'] = implode("\n\n", $parts);
        }
        $validated['builder_blocks'] = $normalized;

        return $validated;
    }
}
