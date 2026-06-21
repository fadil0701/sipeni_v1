@extends('layouts.app')

@section('content')
<style>{!! \App\Services\PanduanPenggunaService::webContentCss() !!}</style>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('panduan.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Panduan</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $title }}</h1>
    </div>
    @if($pdfAvailable)
        <a href="{{ route('panduan.pdf', $doc) }}" target="_blank" rel="noopener"
           class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Unduh PDF
        </a>
    @endif
</div>

<div class="grid gap-6 lg:grid-cols-4">
    <aside class="lg:col-span-1 space-y-4">
        @if(count($roleGuides))
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-blue-800">Role Anda</h2>
            <ul class="mt-2 space-y-1">
                @foreach($roleGuides as $guide)
                    <li>
                        <a href="{{ route('panduan.show', $guide['slug']) }}"
                           class="block rounded px-2 py-1.5 text-sm {{ $doc === $guide['slug'] ? 'bg-blue-600 text-white' : 'text-blue-900 hover:bg-blue-100' }}">
                            {{ $guide['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bab umum</h2>
            <ul class="mt-2 space-y-1">
                @foreach($chapters as $chapter)
                    <li>
                        <a href="{{ route('panduan.show', $chapter['slug']) }}"
                           class="block rounded px-2 py-1.5 text-sm {{ $doc === $chapter['slug'] ? 'bg-gray-800 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            {{ $chapter['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>

    <article class="lg:col-span-3 rounded-xl border border-gray-200 bg-white p-6 shadow-sm panduan-content">
        {!! $html !!}
    </article>
</div>
@endsection
