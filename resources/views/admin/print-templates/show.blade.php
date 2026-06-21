@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $printTemplate->name }}</h1>
        <p class="mt-1 text-sm font-mono text-gray-600">{{ $printTemplate->key }}</p>
        <p class="mt-1 text-xs text-gray-500">Kertas: <span class="font-mono">{{ strtoupper($printTemplate->paper_size ?? 'a4') }}</span> · {{ ($printTemplate->orientation ?? 'portrait') === 'landscape' ? 'Lanskap' : 'Potret' }} · margin {{ (int) ($printTemplate->print_margin_mm ?? 12) }} mm</p>
        @if($printTemplate->description)
            <p class="mt-2 text-sm text-gray-700">{{ $printTemplate->description }}</p>
        @endif
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.print-templates.pdf', $printTemplate) }}" class="inline-flex px-4 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-800 hover:bg-gray-50">PDF contoh</a>
        <a href="{{ route('admin.print-templates.preview', $printTemplate) }}" target="_blank" rel="noopener" class="inline-flex px-4 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Pratinjau cetak</a>
        <a href="{{ route('admin.print-templates.edit', $printTemplate) }}" class="inline-flex px-4 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-50">Edit</a>
        <a href="{{ route('admin.print-templates.index') }}" class="inline-flex px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800">← Daftar</a>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-3">Variabel (template + data contoh + payload)</h2>
        <p class="mb-3 text-xs text-gray-500">Gabungan placeholder di Header/Isi, kunci dari <strong>sample_data</strong>, dan variabel bawaan aplikasi jika <strong>key</strong> dikenal.</p>
        @if(count($placeholderGroups['raw']) || count($placeholderGroups['escaped']))
            <div class="space-y-4 text-sm text-gray-700">
                @if(count($placeholderGroups['raw']))
                    <div>
                        <p class="mb-1 text-xs font-medium text-amber-900">HTML mentah <code class="rounded bg-amber-50 px-1">{{ '{' }}{{ '{' }}{{ '{' }}…{{ '}' }}{{ '}' }}{{ '}' }}</code></p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($placeholderGroups['raw'] as $ph)
                                <li><code class="rounded bg-gray-100 px-1 font-mono text-xs">{{ $ph }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(count($placeholderGroups['escaped']))
                    <div>
                        <p class="mb-1 text-xs font-medium text-slate-800">Teks di-escape <code class="rounded bg-slate-100 px-1">{{ '{' }}{{ '{' }}…{{ '}' }}{{ '}' }}</code></p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($placeholderGroups['escaped'] as $ph)
                                <li><code class="rounded bg-gray-100 px-1 font-mono text-xs">{{ $ph }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-500">Belum ada variabel — tambahkan placeholder di template, isi JSON contoh, atau gunakan key yang punya payload terdaftar.</p>
        @endif
    </div>
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-3">Pemakaian dari kode</h2>
        @php
            $codeSnippet = '$tpl = \\App\\Models\\PrintTemplate::where(\'key\', '.json_encode($printTemplate->key, JSON_UNESCAPED_UNICODE).')
    ->where(\'is_active\', true)
    ->first();
$html = $tpl
    ? \\App\\Services\\PrintTemplateRenderer::render($tpl, $dataArray)
    : view(\'fallback.blade\', compact(...))->render();';
        @endphp
        <pre class="text-xs bg-gray-900 text-gray-100 p-4 rounded-md overflow-x-auto"><code>{{ $codeSnippet }}</code></pre>
        <p class="mt-3 text-xs text-gray-600">Kunci <code class="bg-gray-100 px-1 rounded">nested.key</code> didukung lewat <code class="bg-gray-100 px-1 rounded">data_get</code>. <code class="bg-gray-100 px-1 rounded">{!! e('{{kunci}}') !!}</code> di-escape; <code class="bg-gray-100 px-1 rounded">{!! e('{{{kunci}}}') !!}</code> menyisipkan HTML mentah (hanya dari kode tepercaya).</p>
    </div>
</div>
@endsection
