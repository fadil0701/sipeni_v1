@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar KIR
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Kartu Inventaris Ruangan</h2>
        <p class="mt-1 text-sm text-gray-600">Tempatkan aset ke ruangan tertentu</p>
    </div>
    
    <form action="{{ route('asset.kartu-inventaris-ruangan.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Register Aset -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Register Aset</h3>
                <div>
                    <label for="id_register_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Register Aset <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_register_aset" 
                        name="id_register_aset" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_register_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Register Aset</option>
                        @foreach($registerAsets as $registerAset)
                            <option
                                value="{{ $registerAset->id_register_aset }}"
                                data-id-unit-kerja="{{ $registerAset->id_unit_kerja }}"
                                {{ old('id_register_aset') == $registerAset->id_register_aset ? 'selected' : '' }}
                            >
                                {{ $registerAset->nomor_register }} - {{ $registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                                @if($registerAset->unitKerja)
                                    — {{ $registerAset->unitKerja->nama_unit_kerja }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('id_register_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih register aset yang akan ditempatkan di ruangan</p>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="kir_create_id_ruangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="kir_create_id_ruangan" 
                        name="id_ruangan" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan') border-red-500 @enderror"
                    >
                        <option value="">Pilih register aset terlebih dahulu</option>
                    </select>
                    @error('id_ruangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kir_create_id_penanggung_jawab" class="block text-sm font-medium text-gray-700 mb-2">
                        Penanggung Jawab <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="kir_create_id_penanggung_jawab" 
                        name="id_penanggung_jawab" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_penanggung_jawab') border-red-500 @enderror"
                    >
                        <option value="">Pilih register aset terlebih dahulu</option>
                    </select>
                    @error('id_penanggung_jawab')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_penempatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Penempatan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_penempatan" 
                        name="tanggal_penempatan" 
                        value="{{ old('tanggal_penempatan', date('Y-m-d')) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_penempatan') border-red-500 @enderror"
                    >
                    @error('tanggal_penempatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('asset.kartu-inventaris-ruangan.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan KIR
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const API_RUANGAN = @json(url('/api/master/ruangan-by-unit'));
    const API_PEGAWAI = @json(url('/api/master/pegawai-by-unit'));
    const regSel = document.getElementById('id_register_aset');
    const ruangSel = document.getElementById('kir_create_id_ruangan');
    const pjSel = document.getElementById('kir_create_id_penanggung_jawab');
    const oldRuangan = @json(old('id_ruangan'));
    const oldPj = @json(old('id_penanggung_jawab'));

    function fillSelect(selectEl, placeholder, rows, valueKey, labelKey) {
        if (!selectEl) return;
        const prev = selectEl.value;
        selectEl.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        selectEl.appendChild(opt0);
        (rows || []).forEach(function (row) {
            const o = document.createElement('option');
            o.value = String(row[valueKey]);
            o.textContent = row[labelKey];
            selectEl.appendChild(o);
        });
        const want = oldRuangan && selectEl === ruangSel ? String(oldRuangan) : (oldPj && selectEl === pjSel ? String(oldPj) : prev);
        if (want && Array.from(selectEl.options).some(function (op) { return op.value === want; })) {
            selectEl.value = want;
        }
    }

    async function loadForUnit(unitId) {
        if (!unitId) {
            fillSelect(ruangSel, 'Pilih register aset terlebih dahulu', [], 'id_ruangan', 'label');
            fillSelect(pjSel, 'Pilih register aset terlebih dahulu', [], 'id', 'label');
            return;
        }
        try {
            const [rRes, pRes] = await Promise.all([
                fetch(API_RUANGAN + '/' + encodeURIComponent(unitId), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }),
                fetch(API_PEGAWAI + '/' + encodeURIComponent(unitId), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }),
            ]);
            const rJson = await rRes.json();
            const pJson = await pRes.json();
            fillSelect(ruangSel, 'Pilih Ruangan', rJson.data || [], 'id_ruangan', 'label');
            fillSelect(pjSel, 'Pilih Penanggung Jawab', pJson.data || [], 'id', 'label');
        } catch (e) {
            fillSelect(ruangSel, 'Gagal memuat ruangan', [], 'id_ruangan', 'label');
            fillSelect(pjSel, 'Gagal memuat pegawai', [], 'id', 'label');
        }
    }

    function onRegisterChange() {
        if (!regSel) return;
        const opt = regSel.options[regSel.selectedIndex];
        const unitId = opt ? opt.getAttribute('data-id-unit-kerja') : '';
        loadForUnit(unitId);
    }

    if (regSel) {
        regSel.addEventListener('change', onRegisterChange);
        if (regSel.value) {
            onRegisterChange();
        }
    }
})();
</script>
@endpush
@endsection
