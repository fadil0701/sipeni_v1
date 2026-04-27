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
        <h2 class="text-xl font-semibold text-gray-900">Tambah Jadwal Pemeliharaan Rutin</h2>
    </div>
    <form action="{{ route('maintenance.jadwal-maintenance.store') }}" method="POST" class="p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Register Aset</label>
            <select name="id_register_aset" required class="block w-full px-3 py-2 border border-gray-300 rounded-md @error('id_register_aset') border-red-500 @enderror">
                <option value="">Pilih aset</option>
                @foreach($registerAsets as $aset)
                    <option value="{{ $aset->id_register_aset }}" @selected(old('id_register_aset') == $aset->id_register_aset)>{{ $aset->nomor_register }} - {{ $aset->inventory->dataBarang->nama_barang ?? '-' }}</option>
                @endforeach
            </select>
            @error('id_register_aset')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                <select name="jenis_maintenance" required class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['RUTIN','KALIBRASI','PERBAIKAN','PENGGANTIAN_SPAREPART'] as $jenis)
                        <option value="{{ $jenis }}" @selected(old('jenis_maintenance') === $jenis)>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <select id="periode" name="periode" required class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['HARIAN','MINGGUAN','BULANAN','3_BULAN','6_BULAN','TAHUNAN','CUSTOM'] as $periode)
                        <option value="{{ $periode }}" @selected(old('periode','BULANAN') === $periode)>{{ $periode }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Interval Hari (Custom)</label>
                <input id="interval_hari" type="number" min="1" name="interval_hari" value="{{ old('interval_hari') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" required value="{{ old('tanggal_mulai', now()->toDateString()) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
            <textarea name="keterangan" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('keterangan') }}</textarea>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const periode = document.getElementById('periode');
    const interval = document.getElementById('interval_hari');
    function toggleInterval() {
        const enabled = periode.value === 'CUSTOM';
        interval.disabled = !enabled;
        interval.required = enabled;
    }
    periode.addEventListener('change', toggleInterval);
    toggleInterval();
</script>
@endpush
@endsection
