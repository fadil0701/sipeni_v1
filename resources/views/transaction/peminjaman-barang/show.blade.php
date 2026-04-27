@extends('layouts.app')

@php($user = auth()->user())
@php($pegawai = $user?->pegawai)

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.peminjaman-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Peminjaman
    </a>
</div>

@if(session('success'))
    <div class="alert-box alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-box alert-error mb-4">{{ session('error') }}</div>
@endif

<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Detail Peminjaman</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $peminjaman->no_peminjaman }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $peminjaman->status_badge_class }}">{{ $peminjaman->status_label }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 px-6 py-5 sm:grid-cols-2">
        <div><span class="text-xs text-gray-500">Unit Peminjam</span><p class="text-sm font-medium text-gray-900">{{ $peminjaman->unitPeminjam->nama_unit_kerja ?? '-' }}</p></div>
        <div><span class="text-xs text-gray-500">Pemohon</span><p class="text-sm font-medium text-gray-900">{{ $peminjaman->pemohon->nama_pegawai ?? '-' }}</p></div>
        <div><span class="text-xs text-gray-500">Tujuan</span><p class="text-sm font-medium text-gray-900">{{ $peminjaman->tujuan_label }}</p></div>
        <div>
            <span class="text-xs text-gray-500">Pihak Tujuan</span>
            <p class="text-sm font-medium text-gray-900">
                @if($peminjaman->tujuan_peminjaman === \App\Models\PeminjamanBarang::TUJUAN_ANTAR_UNIT_KERJA)
                    {{ $peminjaman->unitPemilik->nama_unit_kerja ?? '-' }}
                @else
                    {{ $peminjaman->gudangPusat->nama_gudang ?? '-' }}
                @endif
            </p>
        </div>
        <div><span class="text-xs text-gray-500">Tanggal Pinjam</span><p class="text-sm font-medium text-gray-900">{{ optional($peminjaman->tanggal_pinjam)->format('d-m-Y') }}</p></div>
        <div><span class="text-xs text-gray-500">Rencana Kembali</span><p class="text-sm font-medium text-gray-900">{{ optional($peminjaman->tanggal_rencana_kembali)->format('d-m-Y') }}</p></div>
        <div class="sm:col-span-2"><span class="text-xs text-gray-500">Alasan</span><p class="text-sm text-gray-900">{{ $peminjaman->alasan ?: '-' }}</p></div>
    </div>

    <div class="border-t border-gray-200 px-6 py-5">
        <h3 class="mb-3 text-base font-semibold text-gray-900">Barang Dipinjam</h3>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Barang</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Satuan</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Kondisi Serah</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Kondisi Kembali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($peminjaman->details as $detail)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $detail->dataBarang->kode_data_barang ?? '-' }} - {{ $detail->dataBarang->nama_barang ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ rtrim(rtrim(number_format((float) $detail->qty_pinjam, 2, '.', ''), '0'), '.') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $detail->kondisi_serah ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $detail->kondisi_kembali ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="border-t border-gray-200 px-6 py-5">
        <h3 class="mb-3 text-base font-semibold text-gray-900">Riwayat Status</h3>
        <div class="space-y-2">
            @forelse($peminjaman->logs as $log)
                <div class="rounded-md border border-gray-200 px-3 py-2 text-sm">
                    <div class="font-medium text-gray-900">{{ strtoupper($log->aksi) }} - {{ $log->status_sesudah ?? '-' }}</div>
                    <div class="text-xs text-gray-500">{{ optional($log->created_at)->format('d-m-Y H:i') }} oleh {{ $log->user->name ?? 'Sistem' }}</div>
                    @if($log->catatan)
                        <div class="mt-1 text-sm text-gray-700">{{ $log->catatan }}</div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada riwayat.</p>
            @endforelse
        </div>
    </div>

    <div class="border-t border-gray-200 px-6 py-5">
        <h3 class="mb-2 text-base font-semibold text-gray-900">Ringkas Alur</h3>
        <ol class="mb-3 list-decimal pl-5 text-sm text-gray-600 space-y-1">
            <li>Pengajuan oleh Unit Peminjam</li>
            <li>Verifikasi oleh Unit Kerja</li>
            <li>Approval + Disposisi oleh Pengurus Barang</li>
            <li>Approval oleh Unit yang Dipinjam (khusus antar unit)</li>
            <li>Diketahui Kasubag TU</li>
            <li>Serah Terima</li>
            <li>Pengembalian oleh Unit Kerja</li>
            <li>Finalisasi pengembalian oleh Pengurus Barang</li>
            <li>Selesai</li>
        </ol>
        <p class="mb-3 text-sm text-gray-600">
            {{ $peminjaman->status_label }}.
            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DIAJUKAN)
                Menunggu verifikasi oleh Unit Kerja.
            @elseif($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A)
                Menunggu approval + disposisi oleh Pengurus Barang.
            @elseif($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B)
                Menunggu approval oleh Unit yang Dipinjam.
            @elseif($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DISETUJUI_PENGURUS)
                Menunggu diketahui Kasubag TU.
            @elseif($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DIKETAHUI_KASUBAG_TU)
                Siap dicatat serah terima.
            @elseif($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_SERAH_TERIMA)
                Menunggu pencatatan pengembalian oleh peminjam.
            @elseif($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_PENGEMBALIAN)
                Menunggu finalisasi selesai oleh pengurus.
            @endif
        </p>
        <h3 class="mb-3 text-base font-semibold text-gray-900">Aksi Workflow</h3>
        <div class="flex flex-wrap gap-2">
            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DIAJUKAN && ($user?->hasRole('admin') || $user?->hasRole('kepala_unit')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.verifikasi-unit-a', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <button type="submit" class="btn-primary-ui">Verifikasi (Unit Kerja)</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A && ($user?->hasRole('admin') || $user?->hasRole('admin_gudang')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.approve-pengurus', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <button type="submit" class="btn-primary-ui">Approve + Disposisi (Pengurus Barang)</button>
                </form>
                <form method="POST" action="{{ route('transaction.peminjaman-barang.reject-pengurus', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <input type="hidden" name="catatan" value="Ditolak pengurus barang">
                    <button type="submit" class="btn-secondary-ui">Tolak (Pengurus Barang)</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B && ($user?->hasRole('admin') || $user?->hasRole('kepala_unit')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.approve-unit-b', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <button type="submit" class="btn-primary-ui">Approve (Unit yang Dipinjam)</button>
                </form>
                <form method="POST" action="{{ route('transaction.peminjaman-barang.reject-unit-b', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <input type="hidden" name="catatan" value="Ditolak oleh Kepala Unit Pemilik">
                    <button type="submit" class="btn-secondary-ui">Tolak (Unit yang Dipinjam)</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_MENUNGGU_APPROVAL_PENGURUS && ($user?->hasRole('admin') || $user?->hasRole('admin_gudang')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.approve-pengurus', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <button type="submit" class="btn-primary-ui">Approve + Disposisi (Pengurus Barang)</button>
                </form>
                <form method="POST" action="{{ route('transaction.peminjaman-barang.reject-pengurus', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <input type="hidden" name="catatan" value="Ditolak pengurus barang">
                    <button type="submit" class="btn-secondary-ui">Tolak (Pengurus Barang)</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DISETUJUI_PENGURUS && ($user?->hasRole('admin') || $user?->hasRole('kasubbag_tu')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.mengetahui-kasubag-tu', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <button type="submit" class="btn-primary-ui">Mengetahui Kasubag TU</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_DIKETAHUI_KASUBAG_TU && ($user?->hasRole('admin') || $user?->hasRole('admin_gudang') || $user?->hasRole('admin_gudang_unit')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.serah-terima', $peminjaman->id_peminjaman) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="kondisi_serah" placeholder="Kondisi serah" required class="rounded-md border border-gray-300 px-2 py-1 text-sm">
                    <button type="submit" class="btn-primary-ui">Catat Serah Terima</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_SERAH_TERIMA && ($user?->hasRole('admin') || $user?->hasRole('pegawai') || $user?->hasRole('kepala_unit')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.pengembalian', $peminjaman->id_peminjaman) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="kondisi_kembali" placeholder="Kondisi kembali" required class="rounded-md border border-gray-300 px-2 py-1 text-sm">
                    <button type="submit" class="btn-primary-ui">Catat Pengembalian</button>
                </form>
            @endif

            @if($peminjaman->status === \App\Models\PeminjamanBarang::STATUS_PENGEMBALIAN && ($user?->hasRole('admin') || $user?->hasRole('admin_gudang')))
                <form method="POST" action="{{ route('transaction.peminjaman-barang.selesai', $peminjaman->id_peminjaman) }}">
                    @csrf
                    <button type="submit" class="btn-primary-ui">Finalisasi Pengembalian (Pengurus)</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

