@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Panduan Pengguna</h1>
    <p class="mt-1 text-sm text-gray-600">Petunjuk penggunaan SI-MANTIK sesuai role dan modul yang Anda akses.</p>
</div>

@if(count($roleGuides))
<div class="mb-6 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-5">
    <h2 class="text-lg font-semibold text-blue-900">Panduan untuk role Anda</h2>
    <p class="mt-1 text-sm text-blue-800">Berdasarkan role pada akun <strong>{{ auth()->user()->name }}</strong>.</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($roleGuides as $guide)
            <a href="{{ route('panduan.show', $guide['slug']) }}"
               class="group rounded-lg border border-blue-100 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-700">{{ $guide['title'] }}</p>
                <p class="mt-1 text-xs text-gray-600">{{ $guide['description'] }}</p>
                <span class="mt-3 inline-flex text-xs font-medium text-blue-600">Buka panduan →</span>
            </a>
        @endforeach
    </div>
</div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Bab umum</h2>
            <p class="mt-1 text-sm text-gray-500">Berlaku untuk semua pengguna.</p>
            <ul class="mt-4 divide-y divide-gray-100">
                @foreach($chapters as $chapter)
                    <li class="py-3 first:pt-0 last:pb-0">
                        <a href="{{ route('panduan.show', $chapter['slug']) }}" class="flex items-start justify-between gap-3 group">
                            <div>
                                <p class="font-medium text-gray-900 group-hover:text-blue-700">{{ $chapter['title'] }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $chapter['description'] }}</p>
                            </div>
                            <span class="shrink-0 text-xs text-blue-600">Baca</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900">Mulai cepat</h3>
            <ol class="mt-3 list-decimal space-y-2 pl-4 text-sm text-gray-600">
                <li><a href="{{ route('panduan.show', 'pengenalan') }}" class="text-blue-600 hover:underline">Login & navigasi</a></li>
                <li><a href="{{ route('panduan.show', 'alur-kerja') }}" class="text-blue-600 hover:underline">Alur permintaan → penerimaan</a></li>
                @if(count($roleGuides))
                    <li><a href="{{ route('panduan.show', $roleGuides[0]['slug']) }}" class="text-blue-600 hover:underline">Panduan role utama Anda</a></li>
                @endif
            </ol>
        </div>

        @if($pdfAvailable)
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-5">
            <h3 class="text-sm font-semibold text-amber-900">Unduh PDF</h3>
            <p class="mt-1 text-xs text-amber-800">Versi cetak untuk dibagikan offline.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('panduan.pdf', 'pengenalan') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-amber-900 ring-1 ring-amber-200 hover:bg-amber-100">
                    PDF Pengenalan
                </a>
                @if(count($roleGuides))
                    <a href="{{ route('panduan.pdf', $roleGuides[0]['slug']) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-amber-900 ring-1 ring-amber-200 hover:bg-amber-100">
                        PDF Role Anda
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
