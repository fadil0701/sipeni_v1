@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.mutasi-aset.show', $mutasiAset->id_mutasi) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Detail
    </a>
</div>

@php
    $idUkAsal = (int) ($mutasiAset->ruanganAsal?->id_unit_kerja ?? $mutasiAset->registerAset?->id_unit_kerja ?? 0);
    $idUkTujuanCurrent = (int) ($mutasiAset->ruanganTujuan?->id_unit_kerja ?? 0);
    $oldUkTujuan = old('id_unit_kerja_tujuan', $idUkTujuanCurrent ?: null);
    $oldAsal = old('id_ruangan_asal', $mutasiAset->id_ruangan_asal);
    $oldTujuan = old('id_ruangan_tujuan', $mutasiAset->id_ruangan_tujuan);
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Mutasi Aset</h2>
        <p class="mt-1 text-sm text-gray-600">Ubah informasi mutasi; ruangan mengikuti master per unit kerja</p>
    </div>

    <form action="{{ route('asset.mutasi-aset.update', $mutasiAset->id_mutasi) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Register Aset</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Register</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $mutasiAset->registerAset->nomor_register ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $mutasiAset->registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                            @if($mutasiAset->registerAset->inventory->jenis_barang ?? null)
                                <span class="text-gray-500 text-sm block">Jenis Barang: {{ $mutasiAset->registerAset->inventory->jenis_barang }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="mutasi_edit_id_ruangan_asal" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan Asal <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_edit_id_ruangan_asal"
                        name="id_ruangan_asal"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan_asal') border-red-500 @enderror"
                    >
                        <option value="">Memuat…</option>
                    </select>
                    @error('id_ruangan_asal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Daftar ruangan sesuai unit kerja pada mutasi (asal).</p>
                </div>

                <div>
                    <label for="mutasi_edit_id_unit_kerja_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                        Unit Kerja Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_edit_id_unit_kerja_tujuan"
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
                    <label for="mutasi_edit_id_ruangan_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutasi_edit_id_ruangan_tujuan"
                        name="id_ruangan_tujuan"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan_tujuan') border-red-500 @enderror"
                    >
                        <option value="">Memuat…</option>
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
                        value="{{ old('tanggal_mutasi', $mutasiAset->tanggal_mutasi ? $mutasiAset->tanggal_mutasi->format('Y-m-d') : '') }}"
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
                    >{{ old('keterangan', $mutasiAset->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a
                href="{{ route('asset.mutasi-aset.show', $mutasiAset->id_mutasi) }}"
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button
                type="submit"
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const ruanganByUnitBase = @json(url('/api/master/ruangan-by-unit'));
    const idUkAsal = @json($idUkAsal);
    const asalSel = document.getElementById('mutasi_edit_id_ruangan_asal');
    const ukTujuanSel = document.getElementById('mutasi_edit_id_unit_kerja_tujuan');
    const tujuanSel = document.getElementById('mutasi_edit_id_ruangan_tujuan');

    const initialAsal = @json($oldAsal);
    const initialTujuan = @json($oldTujuan);
    const initialUkTujuan = @json($oldUkTujuan);

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
        if (!idUkAsal) {
            fillSelect(asalSel, 'Unit kerja asal tidak tersedia', [], null);
            return;
        }
        try {
            const res = await fetch(ruanganUrl(idUkAsal), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const json = await res.json();
            fillSelect(asalSel, 'Pilih Ruangan Asal', json.data || [], initialAsal);
        } catch (e) {
            fillSelect(asalSel, 'Gagal memuat ruangan asal', [], null);
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
            fillSelect(tujuanSel, 'Pilih Ruangan Tujuan', json.data || [], initialTujuan);
        } catch (e) {
            fillSelect(tujuanSel, 'Gagal memuat ruangan tujuan', [], null);
        }
    }

    ukTujuanSel.addEventListener('change', function () {
        loadRuanganTujuan();
    });

    document.addEventListener('DOMContentLoaded', function () {
        loadRuanganAsal();
        if (ukTujuanSel.value) {
            loadRuanganTujuan();
        }
    });
})();
</script>
@endpush
