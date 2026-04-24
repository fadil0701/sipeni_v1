@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Laporan Transaksi</h1>
    <p class="mt-1 text-sm text-gray-600">Ringkasan transaksi operasional</p>
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
