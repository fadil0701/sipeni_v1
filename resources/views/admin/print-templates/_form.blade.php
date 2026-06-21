@php
    // Hindari literal "{{" di dalam @php — Blade tetap mem-parsingnya dan merusak PHP.
    $b = '{';
    $e = '}';
    $defaultBody = '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>'.$b.$b.'judul'.$e.$e.'</title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 2rem; }
    h1 { font-size: 1.25rem; }
    .meta { color: #444; font-size: 0.9rem; }
  </style>
</head>
<body>
  <h1>'.$b.$b.'judul'.$e.$e.'</h1>
  <p class="meta">Tanggal: '.$b.$b.'tanggal'.$e.$e.'</p>
  <p class="meta">Aplikasi: '.$b.$b.'app_name'.$e.$e.'</p>
</body>
</html>';
    $defaultSample = json_encode([
        'judul' => 'Contoh dokumen',
        'tanggal' => now()->format('d/m/Y'),
        'app_name' => config('app.name'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $__header = old('header_html', $printTemplate->header_html ?? '');
    $__body = old('body', $printTemplate->body ?? $defaultBody);
    $__layout = old('layout_mode', $printTemplate->layout_mode ?? 'full_page');
    $__preset = old('header_preset', $printTemplate->header_preset ?? '');
    $__paper = old('paper_size', $printTemplate->paper_size ?? 'a4');
    $__orient = old('orientation', $printTemplate->orientation ?? 'portrait');
    $__margin = (int) old('print_margin_mm', $printTemplate->print_margin_mm ?? 12);
    $__useBuilderDefault = $printTemplate->exists && ! empty($printTemplate->builder_blocks);
    $__useBuilderChecked = filter_var(old('use_block_builder', $__useBuilderDefault ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $__mergedPh = (string) $__header.(string) $__body;
    $placeholderGroupsFromHtml = \App\Services\PrintTemplateRenderer::extractPlaceholderGroups($__mergedPh);

    $__sd = old('sample_data');
    if ($__sd === null) {
        if (! empty($printTemplate->sample_data)) {
            $__sd = json_encode($printTemplate->sample_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $__sd = $printTemplate->exists ? '{}' : $defaultSample;
        }
    }

    $sampleDecoded = null;
    if (is_string($__sd) && trim($__sd) !== '') {
        $tmpSd = json_decode($__sd, true);
        if (is_array($tmpSd)) {
            $sampleDecoded = $tmpSd;
        }
    }
    $tplKey = (string) old('key', $printTemplate->key ?? '');
    $placeholderGroups = \App\Services\PrintTemplateRenderer::mergePlaceholderGroupsWithData(
        $placeholderGroupsFromHtml,
        $sampleDecoded,
        $tplKey
    );

    // Teks bantuan placeholder — jangan pakai @{{ / @{{{ di Blade (merusak parser + textarea).
    $docPhEsc = $b.$b.'nama'.$e.$e;
    $docPhNested = $b.$b.'nested.key'.$e.$e;
    $docPhRaw = $b.$b.$b.'nama'.$e.$e.$e;
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" required maxlength="255" value="{{ old('name', $printTemplate->name ?? '') }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="key" class="block text-sm font-medium text-gray-700 mb-1">Key <span class="text-red-500">*</span></label>
            <input type="text" name="key" id="key" required maxlength="160" pattern="[a-z][a-z0-9._-]*"
                value="{{ old('key', $printTemplate->key ?? '') }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="contoh: surat.pengantar, nota.dinas, distribusi.sbbk">
            <p class="mt-1 text-xs text-gray-500">Identitas stabil untuk dipanggil dari kode: <code class="rounded bg-gray-100 px-1">PrintTemplate::where('key', …)</code>. Boleh apa saja (bukan khusus SBBK).</p>
            @error('key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="layout_mode" class="block text-sm font-medium text-gray-700 mb-1">Jenis template <span class="text-red-500">*</span></label>
            <select name="layout_mode" id="layout_mode" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="full_page" @selected($__layout === 'full_page')>Full page</option>
                <option value="fragment" @selected($__layout === 'fragment')>Fragmen HTML</option>
            </select>
            @error('layout_mode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="header_preset" class="block text-sm font-medium text-gray-700 mb-1">Tipe header</label>
            <select name="header_preset" id="header_preset" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="" @selected($__preset === '')>— Default —</option>
                @foreach(range(1, 6) as $n)
                    @php($val = 'header_'.$n)
                    <option value="{{ $val }}" @selected($__preset === $val)>Header {{ $n }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Hanya label organisasi; isi tetap di editor Header di bawah.</p>
            @error('header_preset')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div>
            <label for="paper_size" class="block text-sm font-medium text-gray-700 mb-1">Ukuran kertas <span class="text-red-500">*</span></label>
            <select name="paper_size" id="paper_size" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="a4" @selected($__paper === 'a4')>A4 (210 × 297 mm)</option>
                <option value="f4" @selected($__paper === 'f4')>F4 / Folio (210 × 330 mm)</option>
            </select>
            @error('paper_size')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="orientation" class="block text-sm font-medium text-gray-700 mb-1">Orientasi <span class="text-red-500">*</span></label>
            <select name="orientation" id="orientation" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="portrait" @selected($__orient === 'portrait')>Potret</option>
                <option value="landscape" @selected($__orient === 'landscape')>Lanskap</option>
            </select>
            @error('orientation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="print_margin_mm" class="block text-sm font-medium text-gray-700 mb-1">Margin cetak (mm) <span class="text-red-500">*</span></label>
            <input type="number" name="print_margin_mm" id="print_margin_mm" min="5" max="30" required value="{{ $__margin }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('print_margin_mm')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Catatan internal</label>
        <textarea name="description" id="description" rows="2" maxlength="5000"
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Opsional — tidak ikut dicetak">{{ old('description', $printTemplate->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950 shadow-sm">
        <p class="font-semibold text-sky-900">Cara kerja (ringkas)</p>
        <ol class="mt-2 list-decimal space-y-2 pl-5 text-sky-900/90 leading-relaxed">
            <li><strong>HTML</strong> (Header + Isi) berisi teks biasa dan <strong>placeholder</strong> berbentuk <code class="rounded bg-white/80 px-1 font-mono text-xs">{{ $docPhEsc }}</code> atau HTML mentah <code class="rounded bg-amber-100 px-1 font-mono text-xs">{{ $docPhRaw }}</code>.</li>
            <li><strong>Data contoh (JSON)</strong> = nilai untuk pratinjau &amp; PDF contoh di admin. Kunci JSON harus <strong>sama</strong> dengan nama placeholder (tanpa kurung). Contoh: placeholder <code class="font-mono text-xs">{{ $docPhEsc }}</code> → di JSON ada <code class="font-mono text-xs">"nama": "..."</code>. Anda bebas menambah field apa pun — <strong>tidak terbatas SBBK</strong>.</li>
            <li><strong>Chip variabel</strong> di bawah = gabungan (a) placeholder yang terdeteksi di HTML, (b) kunci dari JSON contoh, dan (c) <em>opsional</em> daftar variabel bawaan aplikasi jika <strong>Key</strong> Anda terdaftar di <code class="rounded bg-white/80 px-1 font-mono text-xs">config/print_templates.php</code> (saat ini contoh: <code class="font-mono text-xs">distribusi.sbbk</code> untuk cetak distribusi dari kode).</li>
            <li><strong>Cetak dari fitur lain</strong> (data asli): programmer memanggil <code class="rounded bg-white/80 px-1 font-mono text-xs">PrintTemplateRenderer::render($template, $arrayData)</code> dengan <code class="font-mono text-xs">$arrayData</code> berisi kunci yang sama dengan placeholder. Tanpa ubah PHP, template surat baru tetap bisa dipakai lewat JSON + pratinjau.</li>
        </ol>
    </div>

    <div>
        <div class="flex flex-wrap items-end justify-between gap-2 mb-2">
            <label class="block text-sm font-semibold text-gray-800">Variabel</label>
            <p class="text-xs text-gray-500">Chip = placeholder di editor + kunci dari <strong>Data contoh (JSON)</strong> + (jika Key terdaftar di config) variabel payload aplikasi untuk key itu. Klik chip untuk menyisipkan ke editor yang sedang fokus.</p>
        </div>
        <p id="print-template-editor-fallback" class="mb-3 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900" role="status">
            Editor tidak dimuat dari CDN. Edit langsung di kotak teks di bawah.
        </p>
        <div id="print-template-var-chips" class="flex flex-wrap gap-2 rounded-xl border border-gray-200 bg-slate-50 p-3 min-h-[3rem]" data-active-target="print_template_body">
            @foreach($placeholderGroups['raw'] as $k)
                @php($snippetRaw = '{' . '{' . '{' . $k . '}' . '}' . '}')
                <button type="button" class="print-var-chip inline-flex max-w-full items-center rounded-full border border-amber-400/80 bg-amber-600 px-3 py-1 text-left text-xs font-mono text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500" data-snippet="{{ rawurlencode($snippetRaw) }}" title="Sisipkan ke editor fokus">{{ $snippetRaw }}</button>
            @endforeach
            @foreach($placeholderGroups['escaped'] as $k)
                @php($snippetEsc = '{' . '{' . $k . '}' . '}')
                <button type="button" class="print-var-chip inline-flex max-w-full items-center rounded-full border border-slate-500 bg-slate-600 px-3 py-1 text-left text-xs font-mono text-white shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400" data-snippet="{{ rawurlencode($snippetEsc) }}" title="Sisipkan ke editor fokus">{{ $snippetEsc }}</button>
            @endforeach
            @if(! count($placeholderGroups['raw']) && ! count($placeholderGroups['escaped']))
                <span class="text-xs text-gray-500 italic">Belum ada variabel — isi <strong>Key</strong> / JSON contoh, atau tambahkan placeholder di editor, mis. <code class="rounded bg-white px-1 font-mono">{{ '{' }}{{ '{' }}nama{{ '}' }}{{ '}' }}</code> / <code class="rounded bg-white px-1 font-mono">{{ '{' }}{{ '{' }}{{ '{' }}html{{ '}' }}{{ '}' }}{{ '}' }}</code>.</span>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <p class="border-b border-gray-100 bg-slate-50 px-4 py-2 text-center text-sm font-medium text-gray-800">Header</p>
        <div class="p-3 sm:p-4">
            <textarea name="header_html" id="print_template_header" rows="10" maxlength="500000"
                class="block w-full rounded-lg border border-gray-200 font-mono text-sm shadow-inner focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ $__header }}</textarea>
            @error('header_html')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <p class="border-b border-gray-100 bg-slate-50 px-4 py-2 text-center text-sm font-medium text-gray-800">Isi / deskripsi dokumen <span class="text-red-500">*</span></p>
        <div class="p-3 sm:p-4">
            <textarea name="body" id="print_template_body" rows="14" required maxlength="500000"
                class="block w-full rounded-lg border border-gray-200 font-mono text-sm shadow-inner focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ $__body }}</textarea>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <input type="checkbox" name="use_block_builder" id="use_block_builder" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $__useBuilderChecked ? 'checked' : '' }}>
            <label for="use_block_builder" class="text-sm font-semibold text-gray-900">Mode builder blok (drag &amp; drop urutan)</label>
        </div>
        <p class="mt-2 text-xs text-gray-600">Tiap blok berisi HTML; urutan diseret dengan ikon ⋮⋮. Saat disimpan, blok digabung menjadi <strong>Isi</strong> dan disimpan sebagai JSON untuk diedit lagi.</p>
        <div id="print-block-builder-panel" class="mt-4 space-y-3 {{ $__useBuilderChecked ? '' : 'hidden' }}">
            <ul id="print-builder-sortable" class="space-y-2"></ul>
            <button type="button" id="print-builder-add" class="inline-flex items-center rounded-lg border border-indigo-300 bg-white px-3 py-1.5 text-xs font-medium text-indigo-800 hover:bg-indigo-50">+ Tambah blok</button>
            <input type="hidden" name="builder_blocks_json" id="builder_blocks_json" value="{{ old('builder_blocks_json', json_encode($printTemplate->builder_blocks ?? [], JSON_UNESCAPED_UNICODE)) }}">
            @error('builder_blocks_json')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="text-xs text-gray-500 leading-relaxed -mt-4">
        Placeholder teks: <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[11px]">{{ $docPhEsc }}</code> /
        <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[11px]">{{ $docPhNested }}</code>.
        HTML dari payload: <code class="rounded bg-amber-50 px-1.5 py-0.5 font-mono text-[11px] text-amber-900 border border-amber-100">{{ $docPhRaw }}</code> (hanya dari kode tepercaya).
        Saat cetak, <strong>Header</strong> dan <strong>Isi</strong> digabung menjadi satu HTML.
    </p>

    <div>
        <label for="sample_data" class="block text-sm font-medium text-gray-700 mb-1">Data contoh (JSON, opsional)</label>
        <p class="mb-2 text-xs text-gray-600">Ini hanya untuk <strong>pratinjau admin</strong> dan <strong>PDF contoh</strong>. Untuk surat dinamis: tulis kunci apa pun yang Anda pakai di HTML sebagai <code class="rounded bg-gray-100 px-0.5 font-mono">{{ $docPhEsc }}</code>, lalu beri nilai di sini. Contoh panjang SBBK di seeder hanyalah <em>satu</em> pola; surat lain cukup JSON lebih pendek (mis. hanya <code class="font-mono">judul</code>, <code class="font-mono">tanggal</code>, <code class="font-mono">penerima</code>).</p>
        <textarea name="sample_data" id="sample_data" rows="8"
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder='{ "judul": "...", "tanggal": "01/01/2026" }'>{{ $__sd }}</textarea>
        @error('sample_data')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            {{ old('is_active', ($printTemplate->is_active ?? true) ? '1' : '0') === '1' ? 'checked' : '' }}>
        <label for="is_active" class="ml-2 block text-sm text-gray-700">Template aktif</label>
    </div>
</div>

@push('scripts')
<script type="application/json" id="print-template-var-registry">@json(\App\Services\PrintTemplateRenderer::allKnownVariableGroupsByTemplateKey())</script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
@verbatim
<script>
(function () {
    var TINYMCE_BASE = 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1';
    var REGISTRY = {};
    try {
        var regEl = document.getElementById('print-template-var-registry');
        if (regEl && regEl.textContent) {
            REGISTRY = JSON.parse(regEl.textContent);
        }
    } catch (e) {
        REGISTRY = {};
    }

    function flattenSampleKeys(obj, prefix) {
        prefix = prefix || '';
        var out = [];
        if (!obj || typeof obj !== 'object' || Array.isArray(obj)) {
            return out;
        }
        Object.keys(obj).forEach(function (k) {
            var path = prefix ? prefix + '.' + k : k;
            var v = obj[k];
            if (v !== null && typeof v === 'object' && !Array.isArray(v)) {
                out = out.concat(flattenSampleKeys(v, path));
            } else {
                out.push(path);
            }
        });
        return out;
    }

    function knownGroupsForTemplateKey(key) {
        key = String(key || '').trim();
        if (key && REGISTRY[key]) {
            return REGISTRY[key];
        }
        if (key) {
            return { raw: [], escaped: [] };
        }
        var raw = [];
        var esc = [];
        Object.keys(REGISTRY || {}).forEach(function (tk) {
            var g = REGISTRY[tk];
            if (!g) {
                return;
            }
            raw = raw.concat(g.raw || []);
            esc = esc.concat(g.escaped || []);
        });
        function uniqArr(a) {
            var seen = {};
            var out = [];
            for (var i = 0; i < a.length; i++) {
                if (!seen[a[i]]) {
                    seen[a[i]] = true;
                    out.push(a[i]);
                }
            }
            return out;
        }
        raw = uniqArr(raw);
        esc = uniqArr(esc).filter(function (k) {
            return raw.indexOf(k) === -1;
        });
        raw.sort();
        esc.sort();
        return { raw: raw, escaped: esc };
    }

    function mergeVarGroups(editorG, templateKey, sampleText) {
        var known = knownGroupsForTemplateKey(templateKey);
        var knownR = known.raw || [];
        var knownE = known.escaped || [];
        var knownRawSet = {};
        knownR.forEach(function (k) {
            knownRawSet[k] = true;
        });
        var samplePaths = [];
        try {
            var o = JSON.parse(sampleText || '{}');
            if (o && typeof o === 'object' && !Array.isArray(o)) {
                samplePaths = flattenSampleKeys(o);
            }
        } catch (e1) {
            samplePaths = [];
        }
        function uniq(a) {
            var seen = {};
            var out = [];
            for (var i = 0; i < a.length; i++) {
                if (!seen[a[i]]) {
                    seen[a[i]] = true;
                    out.push(a[i]);
                }
            }
            return out;
        }
        var raw = [].concat(editorG.raw || [], knownR);
        var esc = [].concat(editorG.escaped || [], knownE);
        samplePaths.forEach(function (p) {
            var last = p.indexOf('.') === -1 ? p : p.slice(p.lastIndexOf('.') + 1);
            if (knownRawSet[p] || knownRawSet[last]) {
                raw.push(p);
            } else {
                esc.push(p);
            }
        });
        raw = uniq(raw);
        esc = uniq(esc).filter(function (k) {
            return raw.indexOf(k) === -1;
        });
        raw.sort();
        esc.sort();
        return { raw: raw, escaped: esc };
    }

    function wireDataFieldRefresh() {
        var keyEl = document.getElementById('key');
        var sdEl = document.getElementById('sample_data');
        var d = debounce(refreshChips, 400);
        if (keyEl) {
            keyEl.addEventListener('input', d);
            keyEl.addEventListener('change', d);
        }
        if (sdEl) {
            sdEl.addEventListener('input', d);
            sdEl.addEventListener('change', d);
        }
    }
    var RAW_RE = /\{\{\{\s*([a-zA-Z_][a-zA-Z0-9_.]*)\s*\}\}\}/g;
    var ESC_RE = /\{\{\s*([a-zA-Z_][a-zA-Z0-9_.]*)\s*\}\}/g;

    function extractGroups(html) {
        var raw = [];
        var esc = [];
        var m;
        if (!html) {
            return { raw: raw, escaped: esc };
        }
        while ((m = RAW_RE.exec(html)) !== null) {
            raw.push(m[1]);
        }
        RAW_RE.lastIndex = 0;
        while ((m = ESC_RE.exec(html)) !== null) {
            esc.push(m[1]);
        }
        ESC_RE.lastIndex = 0;
        function uniq(a) {
            var seen = {};
            var out = [];
            for (var i = 0; i < a.length; i++) {
                if (!seen[a[i]]) {
                    seen[a[i]] = true;
                    out.push(a[i]);
                }
            }
            return out;
        }
        return { raw: uniq(raw), escaped: uniq(esc) };
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderChips(groups) {
        var raw = groups.raw || [];
        var esc = groups.escaped || [];
        var parts = [];
        raw.forEach(function (k) {
            var sn = '{{{' + k + '}}}';
            var enc = encodeURIComponent(sn);
            parts.push(
                '<button type="button" class="print-var-chip inline-flex max-w-full items-center rounded-full border border-amber-400/80 bg-amber-600 px-3 py-1 text-left text-xs font-mono text-white shadow-sm hover:bg-amber-700" data-snippet="' +
                    enc +
                    '">' +
                    escapeHtml(sn) +
                    '</button>'
            );
        });
        esc.forEach(function (k) {
            var sn = '{{' + k + '}}';
            var enc = encodeURIComponent(sn);
            parts.push(
                '<button type="button" class="print-var-chip inline-flex max-w-full items-center rounded-full border border-slate-500 bg-slate-600 px-3 py-1 text-left text-xs font-mono text-white shadow-sm hover:bg-slate-700" data-snippet="' +
                    enc +
                    '">' +
                    escapeHtml(sn) +
                    '</button>'
            );
        });
        if (!parts.length) {
            parts.push(
                '<span class="text-xs text-gray-500 italic">Belum ada variabel — isi Key / JSON contoh atau placeholder di editor.</span>'
            );
        }
        return parts.join('');
    }

    function mergedSourceFromDom() {
        var hTa = document.getElementById('print_template_header');
        var bTa = document.getElementById('print_template_body');
        var h = typeof tinymce !== 'undefined' && tinymce.get('print_template_header') ? tinymce.get('print_template_header').getContent() : (hTa ? hTa.value : '');
        var b = typeof tinymce !== 'undefined' && tinymce.get('print_template_body') ? tinymce.get('print_template_body').getContent() : (bTa ? bTa.value : '');
        return (h || '') + (b || '');
    }

    function refreshChips() {
        var wrap = document.getElementById('print-template-var-chips');
        if (!wrap) {
            return;
        }
        var active = wrap.getAttribute('data-active-target') || 'print_template_body';
        var keyEl = document.getElementById('key');
        var sampleEl = document.getElementById('sample_data');
        var key = keyEl ? String(keyEl.value || '').trim() : '';
        var sampleText = sampleEl ? String(sampleEl.value || '') : '';
        var merged = mergeVarGroups(extractGroups(mergedSourceFromDom()), key, sampleText);
        wrap.innerHTML = renderChips(merged);
        wrap.setAttribute('data-active-target', active);
    }

    function debounce(fn, ms) {
        var t;
        return function () {
            var args = arguments;
            clearTimeout(t);
            t = setTimeout(function () {
                fn.apply(null, args);
            }, ms);
        };
    }

    function showFallback(show) {
        var fb = document.getElementById('print-template-editor-fallback');
        if (fb) {
            fb.classList.toggle('hidden', !show);
        }
    }

    function wireFormSubmit() {
        var form = document.querySelector('form[action*="print-templates"]');
        if (!form || form.getAttribute('data-print-editor-submit') === '1') {
            return;
        }
        form.setAttribute('data-print-editor-submit', '1');
        form.addEventListener('submit', function () {
            if (typeof syncBuilderHidden === 'function') {
                syncBuilderHidden();
            }
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    }

    var printBuilderSortable = null;

    function uuid() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return String(Date.now()) + '-' + Math.random().toString(16).slice(2);
    }

    function parseBlocksJson(raw) {
        try {
            var o = JSON.parse(raw || '[]');
            return Array.isArray(o) ? o : [];
        } catch (e2) {
            return [];
        }
    }

    function getBlocksFromHidden() {
        var inp = document.getElementById('builder_blocks_json');
        return parseBlocksJson(inp ? inp.value : '[]');
    }

    function syncBuilderHidden() {
        var inp = document.getElementById('builder_blocks_json');
        var list = document.getElementById('print-builder-sortable');
        if (!inp || !list) {
            return;
        }
        var rows = list.querySelectorAll('li[data-block-id]');
        var out = [];
        rows.forEach(function (li) {
            var id = li.getAttribute('data-block-id') || uuid();
            var ta = li.querySelector('.print-builder-block-text');
            out.push({ id: id, html: ta ? String(ta.value) : '' });
        });
        inp.value = JSON.stringify(out);
    }

    function destroyPrintBuilderSortable() {
        if (printBuilderSortable && typeof printBuilderSortable.destroy === 'function') {
            printBuilderSortable.destroy();
        }
        printBuilderSortable = null;
    }

    function renderBuilderBlocks() {
        var list = document.getElementById('print-builder-sortable');
        if (!list) {
            return;
        }
        destroyPrintBuilderSortable();
        var blocks = getBlocksFromHidden();
        list.innerHTML = '';
        blocks.forEach(function (b) {
            var id = (b && b.id) ? String(b.id) : uuid();
            var html = (b && b.html) ? String(b.html) : '';
            var li = document.createElement('li');
            li.setAttribute('data-block-id', id);
            li.className = 'print-builder-row flex gap-2 rounded-lg border border-gray-200 bg-white p-2';
            li.innerHTML =
                '<span class="print-builder-drag cursor-grab select-none self-start pt-1 text-gray-400" title="Seret urutan">⋮⋮</span>' +
                '<textarea class="print-builder-block-text flex-1 rounded border border-gray-200 p-2 font-mono text-xs" rows="6"></textarea>' +
                '<button type="button" class="print-builder-remove self-start rounded px-2 py-0.5 text-sm text-red-600 hover:bg-red-50" title="Hapus blok">×</button>';
            li.querySelector('textarea').value = html;
            list.appendChild(li);
        });
        if (typeof Sortable !== 'undefined') {
            printBuilderSortable = Sortable.create(list, {
                handle: '.print-builder-drag',
                animation: 150,
                onEnd: syncBuilderHidden,
            });
        }
    }

    function wireBuilderToggle() {
        var cb = document.getElementById('use_block_builder');
        var panel = document.getElementById('print-block-builder-panel');
        if (!cb || !panel) {
            return;
        }
        cb.addEventListener('change', function () {
            panel.classList.toggle('hidden', !cb.checked);
        });
    }

    function wireBuilderListDelegation() {
        var list = document.getElementById('print-builder-sortable');
        if (!list || list.getAttribute('data-builder-delegation') === '1') {
            return;
        }
        list.setAttribute('data-builder-delegation', '1');
        list.addEventListener('click', function (e) {
            if (e.target.closest('.print-builder-remove')) {
                var li = e.target.closest('li[data-block-id]');
                if (li && li.parentNode) {
                    li.parentNode.removeChild(li);
                    syncBuilderHidden();
                }
            }
        });
        list.addEventListener(
            'input',
            debounce(function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('print-builder-block-text')) {
                    syncBuilderHidden();
                }
            }, 400)
        );
    }

    function bootBlockBuilder() {
        wireBuilderToggle();
        wireBuilderListDelegation();
        var addBtn = document.getElementById('print-builder-add');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var blocks = getBlocksFromHidden();
                blocks.push({ id: uuid(), html: '<p>Blok baru</p>' });
                document.getElementById('builder_blocks_json').value = JSON.stringify(blocks);
                renderBuilderBlocks();
            });
        }
        renderBuilderBlocks();
    }

    function wireChipContainer() {
        var wrap = document.getElementById('print-template-var-chips');
        if (!wrap || wrap.getAttribute('data-chip-wired') === '1') {
            return;
        }
        wrap.setAttribute('data-chip-wired', '1');
        wrap.addEventListener('click', function (e) {
            var btn = e.target.closest('.print-var-chip');
            if (!btn || !wrap.contains(btn)) {
                return;
            }
            var enc = btn.getAttribute('data-snippet');
            if (!enc) {
                return;
            }
            var text = decodeURIComponent(enc);
            var targetId = wrap.getAttribute('data-active-target') || 'print_template_body';
            if (typeof tinymce !== 'undefined') {
                var ed = tinymce.get(targetId);
                if (ed) {
                    ed.focus();
                    ed.execCommand('mceInsertContent', false, text);
                    return;
                }
            }
            var ta = document.getElementById(targetId);
            if (ta) {
                var start = ta.selectionStart || 0;
                var end = ta.selectionEnd || 0;
                var v = ta.value;
                ta.value = v.slice(0, start) + text + v.slice(end);
                ta.focus();
                ta.selectionStart = ta.selectionEnd = start + text.length;
            }
        });
    }

    function wireEditorFocus(editor) {
        editor.on('focus', function () {
            var z = document.getElementById('print-template-var-chips');
            if (z) {
                z.setAttribute('data-active-target', editor.id);
            }
        });
    }

    function baseEditorConfig(onChangeDebounced) {
        return {
            height: 380,
            base_url: TINYMCE_BASE,
            suffix: '.min',
            branding: false,
            promotion: false,
            menubar: 'edit view insert format tools table help',
            plugins: 'code lists autolink link image table charmap anchor searchreplace visualblocks fullscreen insertdatetime help wordcount',
            toolbar:
                'undo redo | blocks fontsize | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | removeformat | link image table | code fullscreen',
            valid_elements: '*[*]',
            extended_valid_elements: '*[*]',
            verify_html: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            entity_encoding: 'raw',
            content_style: 'body { max-width: 210mm; margin-left: auto; margin-right: auto; padding: 0 8px; }',
            setup: function (editor) {
                wireEditorFocus(editor);
                editor.on('change undo redo keyup SetContent', onChangeDebounced);
            },
            init_instance_callback: function () {
                showFallback(false);
                refreshChips();
            },
        };
    }

    function bootTiny() {
        if (typeof tinymce === 'undefined') {
            showFallback(true);
            return;
        }
        var deb = debounce(refreshChips, 400);
        var p1 = tinymce.init(
            Object.assign({}, baseEditorConfig(deb), {
                selector: '#print_template_header',
            })
        );
        var p2 = tinymce.init(
            Object.assign({}, baseEditorConfig(deb), {
                selector: '#print_template_body',
            })
        );
        function catchP(p) {
            if (p && typeof p.catch === 'function') {
                p.catch(function (err) {
                    if (window.console && window.console.error) {
                        window.console.error('TinyMCE init error', err);
                    }
                    showFallback(true);
                });
            }
        }
        catchP(p1);
        catchP(p2);
    }

    function boot() {
        wireChipContainer();
        wireFormSubmit();
        wireDataFieldRefresh();
        bootBlockBuilder();
        if (typeof tinymce === 'undefined') {
            showFallback(true);
            refreshChips();
            return;
        }
        bootTiny();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endverbatim
@endpush
