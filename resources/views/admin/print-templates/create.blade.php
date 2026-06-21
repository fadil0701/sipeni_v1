@extends('layouts.app')

@section('content')
@php($printTemplate = new \App\Models\PrintTemplate(['is_active' => true, 'layout_mode' => 'full_page', 'paper_size' => 'a4', 'orientation' => 'portrait', 'print_margin_mm' => 12]))
<div class="mx-auto max-w-6xl px-4 sm:px-0 py-4">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah template cetak</h1>
            <p class="mt-1 text-sm text-gray-600">Header dan isi digabung saat render lewat <code class="rounded bg-gray-100 px-1 text-xs">PrintTemplateRenderer::render()</code>.</p>
        </div>
        <a href="{{ route('admin.print-templates.index') }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-medium text-blue-800 shadow-sm hover:bg-blue-50">
            Lihat semua
        </a>
    </div>


    <form method="POST" action="{{ route('admin.print-templates.store') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="p-4 sm:p-6 lg:p-8">
            @include('admin.print-templates._form', ['printTemplate' => $printTemplate])
        </div>
        <div class="flex flex-wrap gap-3 border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
            <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.print-templates.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
        </div>
    </form>
</div>
@endsection
