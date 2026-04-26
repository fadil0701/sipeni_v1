@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Rekap Pemeliharaan per Unit</h1>
    <p class="mt-1 text-sm text-gray-600">Ringkasan aktivitas maintenance berdasarkan periode dan unit kerja.</p>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('reports.maintenance-summary') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div>
            <label for="unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
            <select id="unit_kerja" name="unit_kerja" class="block w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="">Semua Unit</option>
                @foreach($unitKerjas as $unit)
                    <option value="{{ $unit->id_unit_kerja }}" @selected((string) request('unit_kerja') === (string) $unit->id_unit_kerja)>
                        {{ $unit->kode_unit_kerja }} - {{ $unit->nama_unit_kerja }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tanggal_awal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Awal</label>
            <input id="tanggal_awal" name="tanggal_awal" type="date" value="{{ request('tanggal_awal') }}" class="block w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div>
            <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input id="tanggal_akhir" name="tanggal_akhir" type="date" value="{{ request('tanggal_akhir') }}" class="block w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Filter</button>
            <a href="{{ route('reports.maintenance-summary') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
        </div>
    </form>
</div>

<div class="mb-4 flex justify-end">
    <a
        href="{{ route('reports.maintenance-summary.export', request()->query()) }}"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
    >
        Export CSV
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs text-gray-500">Total Aktivitas</p>
        <p class="text-2xl font-semibold text-gray-900">{{ (int) ($summary->total_aktivitas ?? 0) }}</p>
    </div>
    <div class="bg-white border border-green-200 rounded-lg p-4">
        <p class="text-xs text-green-700">Selesai</p>
        <p class="text-2xl font-semibold text-green-700">{{ (int) ($summary->total_selesai ?? 0) }}</p>
    </div>
    <div class="bg-white border border-red-200 rounded-lg p-4">
        <p class="text-xs text-red-700">Gagal</p>
        <p class="text-2xl font-semibold text-red-700">{{ (int) ($summary->total_gagal ?? 0) }}</p>
    </div>
    <div class="bg-white border border-gray-300 rounded-lg p-4">
        <p class="text-xs text-gray-600">Dibatalkan</p>
        <p class="text-2xl font-semibold text-gray-700">{{ (int) ($summary->total_dibatalkan ?? 0) }}</p>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Kerja</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Aktivitas</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selesai</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gagal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dibatalkan</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Biaya</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $row->nama_unit_kerja }}</div>
                            <div class="text-xs text-gray-500">{{ $row->kode_unit_kerja }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">{{ (int) $row->total_aktivitas }}</td>
                        <td class="px-4 py-3 text-sm text-right text-green-700">{{ (int) $row->total_selesai }}</td>
                        <td class="px-4 py-3 text-sm text-right text-red-700">{{ (int) $row->total_gagal }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700">{{ (int) $row->total_dibatalkan }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format((float) $row->total_biaya, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada data riwayat pemeliharaan pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
