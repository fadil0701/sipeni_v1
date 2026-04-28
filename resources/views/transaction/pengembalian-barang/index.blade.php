@extends('layouts.app')

@php($user = auth()->user())

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Pengembalian Barang</h1>
    <p class="mt-1 text-sm text-gray-600">Daftar dokumen peminjaman yang sudah serah terima, sedang dikembalikan, atau telah selesai.</p>
</div>

@if(session('success'))
    <div class="alert-box alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-box alert-error mb-4">{{ session('error') }}</div>
@endif

<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
        <p class="text-xs text-amber-700">Siap Dikembalikan</p>
        <p class="mt-1 text-xl font-semibold text-amber-800">{{ $summary['siap_dikembalikan'] ?? 0 }}</p>
    </div>
    <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-3">
        <p class="text-xs text-indigo-700">Menunggu Finalisasi Pengurus</p>
        <p class="mt-1 text-xl font-semibold text-indigo-800">{{ $summary['menunggu_finalisasi'] ?? 0 }}</p>
    </div>
    <div class="rounded-lg border border-green-200 bg-green-50 p-3">
        <p class="text-xs text-green-700">Selesai</p>
        <p class="mt-1 text-xl font-semibold text-green-800">{{ $summary['selesai'] ?? 0 }}</p>
    </div>
</div>

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form method="GET" action="{{ route('transaction.pengembalian-barang.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach($allowedStatuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ $statusLabels[$status] ?? $status }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Pencarian</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="No peminjaman / unit / pemohon" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary-ui">Terapkan</button>
            <a href="{{ route('transaction.pengembalian-barang.index') }}" class="btn-secondary-ui">Reset</a>
        </div>
    </form>
</div>

<div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No Peminjaman</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Peminjam</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Rencana Kembali</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($list as $item)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $loop->iteration + ($list->currentPage() - 1) * $list->perPage() }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->no_peminjaman }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->unitPeminjam->nama_unit_kerja ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ optional($item->tanggal_rencana_kembali)->format('d-m-Y') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('transaction.peminjaman-barang.show', $item->id_peminjaman) }}" class="text-blue-600 hover:text-blue-800">Detail</a>
                            @if(
                                $item->status === \App\Models\PeminjamanBarang::STATUS_SERAH_TERIMA
                                && $user
                                && (
                                    $user->hasRole('admin')
                                    || (
                                        $user->hasRole('pegawai')
                                        && $pegawai
                                        && (int) $pegawai->id_unit_kerja === (int) $item->id_unit_peminjam
                                    )
                                )
                            )
                                <a href="{{ route('transaction.peminjaman-barang.pengembalian.create', $item->id_peminjaman) }}" class="text-emerald-600 hover:text-emerald-800">Isi Pengembalian</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada dokumen pengembalian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($list->hasPages())
    <div class="mt-4">{{ $list->links() }}</div>
@endif
@endsection
