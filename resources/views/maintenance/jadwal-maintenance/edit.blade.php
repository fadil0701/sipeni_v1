@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Kembali ke Jadwal Pemeliharaan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Jadwal Pemeliharaan</h2>
    </div>
    <form action="{{ route('maintenance.jadwal-maintenance.update', $jadwal->id_jadwal) }}" method="POST" class="p-6 space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Kerja</label>
            <input
                type="text"
                disabled
                value="{{ optional(optional($jadwal->registerAset)->unitKerja)->nama_unit_kerja }}"
                class="block w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50"
            >
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                <select name="jenis_maintenance" required class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['RUTIN','KALIBRASI','PERBAIKAN','PENGGANTIAN_SPAREPART'] as $jenis)
                        <option value="{{ $jenis }}" @selected(old('jenis_maintenance', $jadwal->jenis_maintenance) === $jenis)>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <select id="periode" name="periode" required class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['HARIAN','MINGGUAN','BULANAN','3_BULAN','6_BULAN','TAHUNAN','CUSTOM'] as $periode)
                        <option value="{{ $periode }}" @selected(old('periode', $jadwal->periode) === $periode)>{{ $periode }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Interval Hari</label>
                <input id="interval_hari" type="number" min="1" name="interval_hari" value="{{ old('interval_hari', $jadwal->interval_hari) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" required class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['AKTIF','NONAKTIF','SELESAI'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $jadwal->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" required value="{{ old('tanggal_mulai', optional($jadwal->tanggal_mulai)->format('Y-m-d')) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Terakhir</label>
                <input type="date" disabled value="{{ optional($jadwal->tanggal_terakhir)->format('Y-m-d') }}" class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selanjutnya</label>
                <input type="date" disabled value="{{ optional($jadwal->tanggal_selanjutnya)->format('Y-m-d') }}" class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-md">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
            <textarea name="keterangan" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('keterangan', $jadwal->keterangan) }}</textarea>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button>
        </div>
    </form>
</div>
@endsection
