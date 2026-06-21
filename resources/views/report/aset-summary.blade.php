@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Aset</h1>
        <p class="mt-1 text-sm text-gray-600">Ringkasan status aset dan mutasi</p>
    </div>
    <a href="{{ route('reports.aset-summary.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Export CSV</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg border p-4">
        <p class="text-sm text-gray-500">Aset Aktif</p>
        <p class="text-2xl font-semibold text-gray-900">{{ number_format($asetAktif) }}</p>
    </div>
    <div class="bg-white rounded-lg border p-4">
        <p class="text-sm text-gray-500">Aset Nonaktif</p>
        <p class="text-2xl font-semibold text-gray-900">{{ number_format($asetNonaktif) }}</p>
    </div>
    <div class="bg-white rounded-lg border p-4">
        <p class="text-sm text-gray-500">Mutasi (periode)</p>
        <p class="text-2xl font-semibold text-gray-900">{{ number_format($mutasiCount) }}</p>
    </div>
</div>
@endsection
