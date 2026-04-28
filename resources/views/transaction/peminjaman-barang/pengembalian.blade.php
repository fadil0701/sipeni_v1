@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.peminjaman-barang.show', $peminjaman->id_peminjaman) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Detail Peminjaman
    </a>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Form Pengembalian Barang</h1>
    <p class="mt-1 text-sm text-gray-600">
        Dokumen: <span class="font-medium">{{ $peminjaman->no_peminjaman }}</span> - Unit Peminjam:
        <span class="font-medium">{{ $peminjaman->unitPeminjam->nama_unit_kerja ?? '-' }}</span>
    </p>
</div>

@if($errors->any())
    <div class="alert-box alert-error mb-4">
        <ul class="list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('transaction.peminjaman-barang.pengembalian', $peminjaman->id_peminjaman) }}" class="rounded-lg border border-gray-200 bg-white shadow-sm">
    @csrf
    <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-base font-semibold text-gray-900">Daftar Item Dipinjam</h2>
        <p class="mt-1 text-sm text-gray-600">Isi kondisi kembali untuk setiap item berdasarkan barang yang dipinjam.</p>
    </div>

    <div class="overflow-x-auto px-6 py-5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Barang</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Qty Pinjam</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Satuan</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Kondisi Saat Serah</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Kondisi Kembali <span class="text-red-500">*</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($peminjaman->details as $idx => $detail)
                    @php($oldKondisi = old("items.$idx.kondisi_kembali", $detail->kondisi_kembali))
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            {{ $detail->dataBarang->kode_data_barang ?? '-' }} - {{ $detail->dataBarang->nama_barang ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            {{ rtrim(rtrim(number_format((float) $detail->qty_pinjam, 2, '.', ''), '0'), '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $detail->kondisi_serah ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            <input type="hidden" name="items[{{ $idx }}][id_detail_peminjaman]" value="{{ $detail->id_detail_peminjaman }}">
                            <input
                                type="text"
                                name="items[{{ $idx }}][kondisi_kembali]"
                                value="{{ $oldKondisi }}"
                                required
                                maxlength="100"
                                placeholder="Contoh: Baik / Rusak Ringan"
                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-200 px-6 py-4">
        <a href="{{ route('transaction.peminjaman-barang.show', $peminjaman->id_peminjaman) }}" class="btn-secondary-ui">Batal</a>
        <button type="submit" class="btn-primary-ui">Simpan Pengembalian</button>
    </div>
</form>
@endsection
