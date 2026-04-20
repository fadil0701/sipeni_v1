@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Paket Pengadaan</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $paket->no_paket }}</p>
    </div>
    <a href="{{ route('procurement.paket-pengadaan.index') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
    <form action="{{ route('procurement.paket-pengadaan.update', $paket->id_paket) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="id_sub_kegiatan" class="block text-sm font-medium text-gray-700">Sub Kegiatan <span class="text-red-500">*</span></label>
                <select id="id_sub_kegiatan" name="id_sub_kegiatan" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @foreach($subKegiatanList ?? [] as $sk)
                        <option value="{{ $sk->id_sub_kegiatan }}" {{ old('id_sub_kegiatan', $paket->id_sub_kegiatan) == $sk->id_sub_kegiatan ? 'selected' : '' }}>{{ $sk->nama_sub_kegiatan }} ({{ $sk->kegiatan?->program?->nama_program ?? '-' }})</option>
                    @endforeach
                </select>
                @error('id_sub_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="id_rku" class="block text-sm font-medium text-gray-700">RKU (opsional)</label>
                <select id="id_rku" name="id_rku" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">-- Tanpa RKU --</option>
                    @foreach($rkuList ?? [] as $rku)
                        <option value="{{ $rku->id_rku }}" {{ old('id_rku', $paket->id_rku) == $rku->id_rku ? 'selected' : '' }}>{{ $rku->no_rku }} - {{ $rku->tahun_anggaran }}</option>
                    @endforeach
                </select>
                @error('id_rku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="no_paket" class="block text-sm font-medium text-gray-700">No. Paket <span class="text-red-500">*</span></label>
                <input type="text" id="no_paket" name="no_paket" value="{{ old('no_paket', $paket->no_paket) }}" required maxlength="100" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @error('no_paket')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="nama_paket" class="block text-sm font-medium text-gray-700">Nama Paket <span class="text-red-500">*</span></label>
                <input type="text" id="nama_paket" name="nama_paket" value="{{ old('nama_paket', $paket->nama_paket) }}" required maxlength="255" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @error('nama_paket')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="deskripsi_paket" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea id="deskripsi_paket" name="deskripsi_paket" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('deskripsi_paket', $paket->deskripsi_paket) }}</textarea>
                @error('deskripsi_paket')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="metode_pengadaan" class="block text-sm font-medium text-gray-700">Metode Pengadaan <span class="text-red-500">*</span></label>
                <select id="metode_pengadaan" name="metode_pengadaan" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="PEMILIHAN_LANGSUNG" {{ old('metode_pengadaan', $paket->metode_pengadaan) == 'PEMILIHAN_LANGSUNG' ? 'selected' : '' }}>Pemilihan Langsung</option>
                    <option value="PENUNJUKAN_LANGSUNG" {{ old('metode_pengadaan', $paket->metode_pengadaan) == 'PENUNJUKAN_LANGSUNG' ? 'selected' : '' }}>Penunjukan Langsung</option>
                    <option value="TENDER" {{ old('metode_pengadaan', $paket->metode_pengadaan) == 'TENDER' ? 'selected' : '' }}>Tender</option>
                    <option value="SWAKELOLA" {{ old('metode_pengadaan', $paket->metode_pengadaan) == 'SWAKELOLA' ? 'selected' : '' }}>Swakelola</option>
                </select>
                @error('metode_pengadaan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="nilai_paket" class="block text-sm font-medium text-gray-700">Nilai Paket (Rp) <span class="text-red-500">*</span></label>
                <input type="number" id="nilai_paket" name="nilai_paket" value="{{ old('nilai_paket', $paket->nilai_paket) }}" required min="0" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @error('nilai_paket')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $paket->tanggal_mulai?->format('Y-m-d')) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @error('tanggal_mulai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $paket->tanggal_selesai?->format('Y-m-d')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @error('tanggal_selesai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="status_paket" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                <select id="status_paket" name="status_paket" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="DRAFT" {{ old('status_paket', $paket->status_paket) == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="DIAJUKAN" {{ old('status_paket', $paket->status_paket) == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                    <option value="DIPROSES" {{ old('status_paket', $paket->status_paket) == 'DIPROSES' ? 'selected' : '' }}>Diproses</option>
                    <option value="SELESAI" {{ old('status_paket', $paket->status_paket) == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                    <option value="DIBATALKAN" {{ old('status_paket', $paket->status_paket) == 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                @error('status_paket')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('keterangan', $paket->keterangan) }}</textarea>
                @error('keterangan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('procurement.paket-pengadaan.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
