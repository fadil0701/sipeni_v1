@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-0 py-4">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit template cetak</h1>
            <p class="mt-1 text-sm font-mono text-gray-600">{{ $printTemplate->key }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.print-templates.pdf', $printTemplate) }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">
                PDF contoh
            </a>
            <a href="{{ route('admin.print-templates.preview', $printTemplate) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-800 hover:bg-blue-100">
                Pratinjau
            </a>
            <a href="{{ route('admin.print-templates.index') }}" class="inline-flex items-center rounded-lg border border-blue-300 bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                Lihat semua
            </a>
        </div>
    </div>


    <form method="POST" action="{{ route('admin.print-templates.update', $printTemplate) }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @csrf
        @method('PUT')
        <div class="p-4 sm:p-6 lg:p-8">
            @include('admin.print-templates._form', ['printTemplate' => $printTemplate])
        </div>
        <div class="flex flex-wrap gap-3 border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
            <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Perbarui</button>
            <a href="{{ route('admin.print-templates.preview', $printTemplate) }}" target="_blank" rel="noopener noreferrer" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Pratinjau</a>
            <a href="{{ route('admin.print-templates.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
        </div>
    </form>
</div>
@endsection
