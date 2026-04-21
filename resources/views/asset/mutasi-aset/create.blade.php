@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.mutasi-aset.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Mutasi
    </a>
</div>

@php
    $oldUkTujuan = old('id_unit_kerja_tujuan');
    $oldAsal = old('id_ruangan_asal');
    $oldTujuan = old('id_ruangan_tujuan');
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Mutasi Aset</h2>
        <p class="mt-1 text-sm text-gray-600">Pindahkan aset dari satu ruangan ke ruangan lain (ruangan mengikuti master per unit kerja)</p>
    </div>

    <form action="{{ route('asset.mutasi-aset.store') }}" method="POST" class="p-6">
        @csrf

        <div class="space-y-6">
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Register Aset</h3>
                <div>
                    <label for="mutasi_create_id_register_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Register Aset <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_create_id_register_aset"
                        name="id_register_aset"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_register_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Register Aset</option>
                        @foreach($registerAsets as $registerAset)
                            @php
                                $kir = $registerAset->kartuInventarisRuangan->first();
                            @endphp
                            <option
                                value="{{ $registerAset->id_register_aset }}"
                                data-id-unit-kerja="{{ $registerAset->id_unit_kerja }}"
                                data-ruangan-asal="{{ $kir ? $kir->id_ruangan : '' }}"
                                {{ old('id_register_aset') == $registerAset->id_register_aset ? 'selected' : '' }}
                            >
                                {{ $registerAset->nomor_register }} — {{ $registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                                @if($registerAset->unitKerja)
                                    [{{ $registerAset->unitKerja->nama_unit_kerja }}]
                                @endif
                                @if($kir)
                                    — {{ $kir->ruangan->nama_ruangan ?? '-' }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('id_register_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Register harus sudah memiliki KIR; ruangan asal diambil dari unit kerja register.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="mutasi_create_id_ruangan_asal" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan Asal <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_create_id_ruangan_asal"
                        name="id_ruangan_asal"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan_asal') border-red-500 @enderror"
                    >
                        <option value="">Pilih register aset terlebih dahulu</option>
                    </select>
                    @error('id_ruangan_asal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mutasi_create_id_unit_kerja_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                        Unit Kerja Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_create_id_unit_kerja_tujuan"
                        name="id_unit_kerja_tujuan"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_unit_kerja_tujuan') border-red-500 @enderror"
                    >
                        <option value="">Pilih Unit Kerja Tujuan</option>
                        @foreach($unitKerjas as $uk)
                            <option value="{{ $uk->id_unit_kerja }}" {{ (string) $oldUkTujuan === (string) $uk->id_unit_kerja ? 'selected' : '' }}>
                                {{ $uk->nama_unit_kerja }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_unit_kerja_tujuan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="mutasi_create_id_ruangan_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_create_id_ruangan_tujuan"
                        name="id_ruangan_tujuan"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan_tujuan') border-red-500 @enderror"
                    >
                        <option value="">Pilih unit kerja tujuan terlebih dahulu</option>
                    </select>
                    @error('id_ruangan_tujuan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_mutasi" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mutasi <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="tanggal_mutasi"
                        name="tanggal_mutasi"
                        value="{{ old('tanggal_mutasi', date('Y-m-d')) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_mutasi') border-red-500 @enderror"
                    >
                    @error('tanggal_mutasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan
                    </label>
                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('keterangan') border-red-500 @enderror"
                    >{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a
                href="{{ route('asset.mutasi-aset.index') }}"
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button
                type="submit"
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Mutasi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const ruanganByUnitBase = @json(url('/api/master/ruangan-by-unit'));
    const regSel = document.getElementById('mutasi_create_id_register_aset');
    const ukTujuanSel = document.getElementById('mutasi_create_id_unit_kerja_tujuan');
    const asalSel = document.getElementById('mutasi_create_id_ruangan_asal');
    const tujuanSel = document.getElementById('mutasi_create_id_ruangan_tujuan');

    const oldAsal = @json($oldAsal);
    const oldTujuan = @json($oldTujuan);
    const oldUkTujuan = @json($oldUkTujuan);

    function ruanganUrl(idUk) {
        return ruanganByUnitBase + '/' + encodeURIComponent(idUk);
    }

    function fillSelect(select, placeholder, items, selectedId) {
        select.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = placeholder;
        select.appendChild(ph);
        (items || []).forEach(function (row) {
            const opt = document.createElement('option');
            opt.value = row.id_ruangan;
            opt.textContent = row.label;
            select.appendChild(opt);
        });
        if (selectedId != null && selectedId !== '') {
            select.value = String(selectedId);
        }
    }

    async function loadRuanganAsal() {
        const opt = regSel.options[regSel.selectedIndex];
        const idUk = opt ? opt.getAttribute('data-id-unit-kerja') : null;
        const idKir = opt ? opt.getAttribute('data-ruangan-asal') : '';
        if (!idUk) {
            fillSelect(asalSel, 'Pilih register aset terlebih dahulu', [], null);
            return;
        }
        try {
            const res = await fetch(ruanganUrl(idUk), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const json = await res.json();
            const pick = oldAsal != null && oldAsal !== '' ? oldAsal : idKir;
            fillSelect(asalSel, 'Pilih Ruangan Asal', json.data || [], pick);
        } catch (e) {
            fillSelect(asalSel, 'Gagal memuat ruangan', [], null);
        }
    }

    async function loadRuanganTujuan() {
        const idUk = ukTujuanSel.value;
        if (!idUk) {
            fillSelect(tujuanSel, 'Pilih unit kerja tujuan terlebih dahulu', [], null);
            return;
        }
        try {
            const res = await fetch(ruanganUrl(idUk), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const json = await res.json();
            fillSelect(tujuanSel, 'Pilih Ruangan Tujuan', json.data || [], oldTujuan);
        } catch (e) {
            fillSelect(tujuanSel, 'Gagal memuat ruangan', [], null);
        }
    }

    regSel.addEventListener('change', function () {
        loadRuanganAsal();
    });
    ukTujuanSel.addEventListener('change', function () {
        loadRuanganTujuan();
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (regSel.value) {
            loadRuanganAsal();
        }
        if (ukTujuanSel.value) {
            loadRuanganTujuan();
        }
    });
})();
</script>
@endpush
