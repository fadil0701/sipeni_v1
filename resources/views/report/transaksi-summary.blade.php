@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Transaksi</h1>
        <p class="mt-1 text-sm text-gray-600">Ringkasan transaksi operasional</p>
    </div>
    <a href="{{ route('reports.transaksi-summary.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Export CSV</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg border p-4">
        <p class="text-sm text-gray-500">Distribusi</p>
        <p class="text-2xl font-semibold text-gray-900">{{ number_format($distribusi) }}</p>
    </div>
    <div class="bg-white rounded-lg border p-4">
        <p class="text-sm text-gray-500">Pemakaian</p>
        <p class="text-2xl font-semibold text-gray-900">{{ number_format($pemakaian) }}</p>
    </div>
    <div class="bg-white rounded-lg border p-4">
        <p class="text-sm text-gray-500">Retur</p>
        <p class="text-2xl font-semibold text-gray-900">{{ number_format($retur) }}</p>
    </div>
</div>
@endsection
