@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-start lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Reminder Kedaluwarsa Farmasi</h1>
        <p class="mt-1 text-sm text-gray-600">Farmasi dan persediaan yang memiliki tanggal kedaluwarsa, dengan cakupan gudang sama seperti <strong>Data Stok</strong> (pusat maupun gudang unit kerja Anda).</p>
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        @if($showWarehouseSummaryCards ?? false)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-700">Tampilan</span>
            <a
                href="{{ request()->fullUrlWithQuery(['tampilan' => 'tabel']) }}"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium transition-colors {{ ($tampilan ?? 'tabel') === 'tabel' ? 'border-blue-600 bg-blue-50 text-blue-800 ring-2 ring-blue-500 ring-offset-1' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}"
            >Tabel</a>
            <a
                href="{{ request()->fullUrlWithQuery(['tampilan' => 'cards']) }}"
                class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium transition-colors {{ ($tampilan ?? 'tabel') === 'cards' ? 'border-blue-600 bg-blue-50 text-blue-800 ring-2 ring-blue-500 ring-offset-1' : 'border-gray-300 bg-yellow-600 text-white hover:bg-yellow-700' }}"
            >Ringkasan per gudang</a>
        </div>
        @endif
    <div class="flex flex-wrap gap-2">
        @if(\App\Helpers\PermissionHelper::canAccess(auth()->user(), 'inventory.farmasi-kedaluwarsa.export'))
            <a
                href="{{ route('inventory.farmasi-kedaluwarsa.export', request()->query()) }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700"
            >
                Ekspor CSV
            </a>
        @endif
        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 hover:text-white">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
    </div>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
        <p class="text-xs font-medium text-yellow-900">91–180 hari</p>
        <p class="mt-1 text-2xl font-semibold text-yellow-950">{{ number_format($kpis['range_91_180'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-lg border border-amber-200 bg-yellow-600 text-white p-4">
        <p class="text-xs font-medium text-white">Masa simpan ≤90 hari</p>
        <p class="mt-1 text-2xl font-semibold text-white">{{ number_format($kpis['le_90'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 text-[11px] text-white">Dari hari ini hingga 90 hari ke depan</p>
    </div>
    <div class="rounded-lg border border-red-200 bg-red-600 p-4">
        <p class="text-xs font-medium text-white">Kritis + tinggi (≤30 hari)</p>
        <p class="mt-1 text-2xl font-semibold text-white">{{ number_format($kpis['kritis_tinggi'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 text-[11px] text-white">Termasuk sudah lewat tanggal</p>
    </div>
    <div class="rounded-lg border border-red-200 bg-black p-4">
        <p class="text-xs font-medium text-white">Sudah kedaluwarsa (qty &gt; 0)</p>
        <p class="mt-1 text-2xl font-semibold text-white">{{ number_format($kpis['expired'] ?? 0, 0, ',', '.') }}</p>
    </div>
</div>

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form method="GET" action="{{ route('inventory.farmasi-kedaluwarsa.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-12 lg:items-end">
        <input type="hidden" name="tampilan" value="{{ $tampilan ?? 'tabel' }}">
        <div class="lg:col-span-3">
            <label for="gudang" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
            <select name="gudang" id="gudang" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Semua gudang (data Anda)</option>
                @foreach($gudangs as $g)
                    <option value="{{ $g->id_gudang }}" @selected((string) request('gudang') === (string) $g->id_gudang)>{{ $g->nama_gudang }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-3">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari barang</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama / kode"
                class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div class="lg:col-span-3">
            <label for="prioritas" class="block text-sm font-medium text-gray-700 mb-1">Rentang prioritas</label>
            <select name="prioritas" id="prioritas" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="" @selected(request('prioritas', '') === '')>Default (hingga 180 hari)</option>
                <option value="kritis_tinggi" @selected(request('prioritas') === 'kritis_tinggi')>Kritis + tinggi (≤30 hari)</option>
                <option value="0_90" @selected(request('prioritas') === '0_90')>0–90 hari ke depan</option>
                <option value="91_180" @selected(request('prioritas') === '91_180')>91–180 hari ke depan</option>
                <option value="expired" @selected(request('prioritas') === 'expired')>Hanya sudah kedaluwarsa</option>
            </select>
        </div>
        <div class="lg:col-span-2 flex items-center gap-2 pt-6 lg:pt-0">
            <input type="checkbox" name="include_expired" id="include_expired" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm"
                @checked(request()->boolean('include_expired'))>
            <label for="include_expired" class="text-sm text-gray-700">Sertakan sudah kedaluwarsa</label>
        </div>
        <div class="lg:col-span-1 flex gap-2">
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
        </div>
    </form>
    <p class="mt-3 text-xs text-gray-500">Prioritas baris: <strong>Kritis</strong> (kedaluwarsa atau ≤7 hari), <strong>Tinggi</strong> (8–30 hari), <strong>Sedang</strong> (31–90 hari), <strong>Rendah</strong> (91–180 hari).</p>
</div>

@if($showWarehouseSummaryCards ?? false)
@include('partials.gudang-stock-summary-cards', [
    'routeName' => 'inventory.farmasi-kedaluwarsa.index',
    'helpText' => 'Ringkasan mengikuti filter di atas (tanpa filter gudang pada dropdown). Setiap kartu menjumlahkan baris inventory dan total qty untuk gudang tersebut. Klik kartu untuk membuka tabel rinci hanya pada gudang itu.',
    'emptyMessage' => 'Tidak ada gudang dengan data reminder untuk filter saat ini.',
])
@endif

@if(($tampilan ?? 'tabel') === 'tabel')
<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Barang</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Jenis</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Batch</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Kedaluwarsa</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Sisa hari</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Prioritas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Gudang</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Satuan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Merk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($rows as $row)
                    @php
                        $meta = \App\Services\FarmasiExpiryReminderService::decorateRowForView($row, $today);
                        $badge = match ($meta['prioritas']) {
                            \App\Services\FarmasiExpiryReminderService::PRIORITAS_KRITIS => 'bg-red-100 text-red-900',
                            \App\Services\FarmasiExpiryReminderService::PRIORITAS_TINGGI => 'bg-orange-100 text-orange-900',
                            \App\Services\FarmasiExpiryReminderService::PRIORITAS_SEDANG => 'bg-amber-100 text-amber-950',
                            \App\Services\FarmasiExpiryReminderService::PRIORITAS_RENDAH => 'bg-green-100 text-green-900',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $row->dataBarang->nama_barang ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $row->dataBarang->kode_data_barang ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->jenis_inventory ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $row->no_batch ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $row->tanggal_kedaluwarsa ? $row->tanggal_kedaluwarsa->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium {{ $meta['sisa_hari'] < 0 ? 'text-red-700' : 'text-gray-900' }}">
                            {{ $meta['sisa_hari'] }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $meta['prioritas_label'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $row->gudang->nama_gudang ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format((float) $row->qty_input, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->merk ? $row->merk : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada data yang cocok dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rows->hasPages())
        <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">{{ $rows->links() }}</div>
    @endif
</div>
@else
    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-600">
        Tabel disembunyikan pada tampilan ringkasan. Pilih kartu gudang di atas atau ubah ke <strong>Tabel</strong>.
    </div>
@endif
@endsection
