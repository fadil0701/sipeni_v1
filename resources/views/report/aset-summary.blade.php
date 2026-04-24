@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Laporan Aset</h1>
    <p class="mt-1 text-sm text-gray-600">Ringkasan status aset dan mutasi</p>
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
