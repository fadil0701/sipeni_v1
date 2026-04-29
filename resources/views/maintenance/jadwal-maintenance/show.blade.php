@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Kembali ke Jadwal Pemeliharaan
    </a>
</div>
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Detail Jadwal Pemeliharaan</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Unit Kerja</dt><dd class="font-medium">{{ optional(optional($jadwal->registerAset)->unitKerja)->nama_unit_kerja ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Nomor Register</dt><dd class="font-medium">{{ $jadwal->registerAset->nomor_register ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Jenis</dt><dd class="font-medium">{{ $jadwal->jenis_maintenance }}</dd></div>
        <div><dt class="text-gray-500">Periode</dt><dd class="font-medium">{{ $jadwal->periode }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Mulai</dt><dd class="font-medium">{{ optional($jadwal->tanggal_mulai)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Selanjutnya</dt><dd class="font-medium">{{ optional($jadwal->tanggal_selanjutnya)->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Tanggal Terakhir</dt><dd class="font-medium">{{ optional($jadwal->tanggal_terakhir)->format('d/m/Y') ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $jadwal->status }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Keterangan</dt><dd class="font-medium whitespace-pre-wrap">{{ $jadwal->keterangan ?: '-' }}</dd></div>
    </dl>
    <div class="mt-6 flex space-x-3">
        <a href="{{ route('maintenance.jadwal-maintenance.edit', $jadwal->id_jadwal) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md">Edit</a>
        @if($jadwal->status === 'AKTIF')
            <form method="POST" action="{{ route('maintenance.jadwal-maintenance.generate-permintaan', $jadwal->id_jadwal) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Generate Permintaan Rutin</button>
            </form>
        @endif
    </div>
</div>
@endsection
