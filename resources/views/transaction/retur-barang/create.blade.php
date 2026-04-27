@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.retur-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Retur
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Retur Barang (Terpisah dari Peminjaman)</h2>
    </div>
    
    <form action="{{ route('transaction.retur-barang.store') }}" method="POST" class="p-6" id="formRetur">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Retur -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Retur</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_retur" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Retur <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_retur" 
                            name="tanggal_retur" 
                            required
                            value="{{ old('tanggal_retur', date('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_retur') border-red-500 @enderror"
                        >
                        @error('tanggal_retur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_unit_kerja" 
                            name="id_unit_kerja" 
                            required
                            onchange="loadByUnitKerja(this.value)"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_unit_kerja') border-red-500 @enderror"
                        >
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja') == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                                    {{ $unitKerja->nama_unit_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_unit_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang_asal" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang Asal (Unit) <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang_asal" 
                            name="id_gudang_asal" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_asal') border-red-500 @enderror"
                        >
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_asal') == $gudang->id_gudang ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }} ({{ $gudang->jenis_gudang }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_gudang_asal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang Tujuan (Pusat) <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang_tujuan" 
                            name="id_gudang_tujuan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_tujuan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($gudangs as $gudang)
                                @if($gudang->jenis_gudang == 'PUSAT')
                                    <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_tujuan') == $gudang->id_gudang ? 'selected' : '' }}>
                                        {{ $gudang->nama_gudang }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('id_gudang_tujuan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_pegawai_pengirim" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Pengirim <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pegawai_pengirim" 
                            name="id_pegawai_pengirim" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_pengirim') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pegawai Pengirim</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_pengirim') == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_pengirim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_retur" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Retur <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="status_retur" 
                            name="status_retur" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_retur') border-red-500 @enderror"
                        >
                            <option value="">Pilih Status</option>
                            <option value="DRAFT" {{ old('status_retur', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="DIAJUKAN" {{ old('status_retur') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                        </select>
                        @error('status_retur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="jenis_retur" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Retur <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="jenis_retur"
                            name="jenis_retur"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_retur') border-red-500 @enderror"
                        >
                            @foreach($jenisReturOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('jenis_retur', 'RUSAK') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('jenis_retur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="alasan_retur" class="block text-sm font-medium text-gray-700 mb-2">Alasan Retur</label>
                        <textarea 
                            id="alasan_retur" 
                            name="alasan_retur" 
                            rows="3"
                            placeholder="Masukkan alasan retur barang (contoh: rusak saat penggunaan)"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('alasan_retur') }}</textarea>
                    </div>

                </div>
            </div>

            <!-- Detail Retur -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Detail Retur</h3>
                    <button
                        type="button"
                        id="addDetailRowBtn"
                        class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                    >
                        + Add Row
                    </button>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full table-fixed divide-y divide-gray-200">
                        <colgroup>
                            <col class="w-[30%]">
                            <col class="w-[14%]">
                            <col class="w-[14%]">
                            <col class="w-[16%]">
                            <col class="w-[20%]">
                            <col class="w-[6%]">
                        </colgroup>
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Barang</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Qty Diterima</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Qty Retur</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Satuan</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Alasan Item</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detailContainer" class="divide-y divide-gray-200 bg-white">
                            <!-- Item akan ditambahkan di sini via JavaScript -->
                        </tbody>
                    </table>
                </div>

                @error('detail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('transaction.retur-barang.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan
            </button>
        </div>
    </form>
</div>

<!-- Template untuk item detail (hidden) -->
<template id="itemTemplate">
    <tr class="item-row">
        <td class="px-3 py-2 align-middle">
            <select
                name="detail[INDEX][id_inventory]"
                required
                class="select-barang block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
            >
                <option value="">Pilih Barang</option>
            </select>
        </td>
        <td class="px-3 py-2 align-middle">
            <input
                type="text"
                class="qty-diterima block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                readonly
            >
        </td>
        <td class="px-3 py-2 align-middle">
            <input
                type="number"
                name="detail[INDEX][qty_retur]"
                required
                min="0"
                step="0.01"
                placeholder="0"
                class="qty-retur-input block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
            >
        </td>
        <td class="px-3 py-2 align-middle">
            <select
                name="detail[INDEX][id_satuan]"
                required
                class="select-satuan block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
            >
                <option value="">Pilih Satuan</option>
                @foreach($satuans as $satuan)
                    <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2 align-middle">
            <input
                type="text"
                name="detail[INDEX][alasan_retur_item]"
                placeholder="Opsional"
                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
            >
        </td>
        <td class="px-3 py-2 text-center align-middle">
            <button
                type="button"
                class="remove-row inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                title="Hapus baris"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let inventoryOptions = [];
const urlPegawaiByUnit = @json(url('/api/master/pegawai-by-unit'));
const urlGudangByUnit = @json(url('/api/master/gudang-by-unit'));
const urlInventoryByUnit = @json(url('/api/master/inventory-by-unit'));
const oldGudangAsal = @json(old('id_gudang_asal'));
const oldPegawai = @json(old('id_pegawai_pengirim'));

function loadByUnitKerja(unitId) {
    const asalSel = document.getElementById('id_gudang_asal');
    const pegawaiSel = document.getElementById('id_pegawai_pengirim');
    if (!unitId) {
        asalSel.innerHTML = '<option value="">Pilih Gudang Asal</option>';
        pegawaiSel.innerHTML = '<option value="">Pilih Pegawai Pengirim</option>';
        return;
    }

    asalSel.innerHTML = '<option value="">Memuat Gudang...</option>';
    fetch(`${urlGudangByUnit}/${unitId}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            asalSel.innerHTML = '<option value="">Pilih Gudang Asal</option>';
            (data.data || []).forEach(g => {
                if (g.jenis_gudang !== 'UNIT') return;
                const opt = document.createElement('option');
                opt.value = g.id_gudang;
                opt.textContent = g.label;
                asalSel.appendChild(opt);
            });
            if (oldGudangAsal) asalSel.value = String(oldGudangAsal);
        });

    pegawaiSel.innerHTML = '<option value="">Memuat Pegawai...</option>';
    fetch(`${urlPegawaiByUnit}/${unitId}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            pegawaiSel.innerHTML = '<option value="">Pilih Pegawai Pengirim</option>';
            (data.data || []).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.label;
                pegawaiSel.appendChild(opt);
            });
            if (oldPegawai) pegawaiSel.value = String(oldPegawai);
        });

    fetch(`${urlInventoryByUnit}/${unitId}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            inventoryOptions = data.data || [];
            document.getElementById('detailContainer').innerHTML = '';
            detailRowIndex = 0;
            if (inventoryOptions.length > 0) {
                const row = createDetailRow();
                if (row) document.getElementById('detailContainer').appendChild(row);
            }
        });
}

let detailRowIndex = 0;

function createDetailRow(prefill = {}) {
    const template = document.getElementById('itemTemplate');
    if (!template) return null;

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, detailRowIndex++);
    const row = tempDiv.firstElementChild;
    if (!row) return null;

    const barangSelect = row.querySelector('.select-barang');
    const qtyDiterimaInput = row.querySelector('.qty-diterima');
    const qtyReturInput = row.querySelector('.qty-retur-input');
    const satuanSelect = row.querySelector('.select-satuan');

    if (barangSelect) {
        inventoryOptions.forEach((detail) => {
            const opt = document.createElement('option');
            opt.value = String(detail.id_inventory);
            opt.textContent = detail.label || detail.nama_barang;
            barangSelect.appendChild(opt);
        });
    }

    const syncRowByInventory = (idInventory) => {
        const selected = inventoryOptions.find((d) => String(d.id_inventory) === String(idInventory));
        if (!selected) {
            qtyDiterimaInput.value = '';
            qtyReturInput.max = '';
            if (!prefill.id_satuan) satuanSelect.value = '';
            return;
        }

        qtyDiterimaInput.value = selected.qty_tersedia ?? '0';
        qtyReturInput.max = selected.qty_tersedia ?? '0';
        if (!prefill.id_satuan) satuanSelect.value = selected.id_satuan ?? '';
    };

    barangSelect?.addEventListener('change', function () {
        syncRowByInventory(this.value);
    });

    row.querySelector('.remove-row')?.addEventListener('click', function () {
        row.remove();
    });

    if (prefill.id_inventory && barangSelect) {
        barangSelect.value = String(prefill.id_inventory);
        syncRowByInventory(prefill.id_inventory);
    }
    if (prefill.qty_retur && qtyReturInput) qtyReturInput.value = prefill.qty_retur;
    if (prefill.id_satuan && satuanSelect) satuanSelect.value = String(prefill.id_satuan);
    const alasanInput = row.querySelector('[name*="[alasan_retur_item]"]');
    if (prefill.alasan_retur_item && alasanInput) alasanInput.value = prefill.alasan_retur_item;

    return row;
}

// Load penerimaan detail jika sudah dipilih
document.addEventListener('DOMContentLoaded', function() {
    const unitId = document.getElementById('id_unit_kerja').value;
    if (unitId) loadByUnitKerja(unitId);

    document.getElementById('addDetailRowBtn')?.addEventListener('click', function () {
        if (!inventoryOptions.length) {
            alert('Pilih unit kerja terlebih dahulu.');
            return;
        }
        const row = createDetailRow();
        if (row) document.getElementById('detailContainer').appendChild(row);
    });
    
    // Validasi form sebelum submit
    const formRetur = document.getElementById('formRetur');
    if (formRetur) {
        formRetur.addEventListener('submit', function(e) {
            const detailContainer = document.getElementById('detailContainer');
            const detailRows = detailContainer.querySelectorAll('.item-row');
            
            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Detail retur belum diisi. Tambahkan minimal 1 baris item.');
                return false;
            }
            
            // Validasi setiap item
            let isValid = true;
            let emptyFields = [];
            detailRows.forEach((row, index) => {
                const idInventory = row.querySelector('[name*="[id_inventory]"]');
                const qtyRetur = row.querySelector('[name*="[qty_retur]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                const qtyDiterima = parseFloat(row.querySelector('.qty-diterima').value || 0);
                
                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Inventory`);
                }
                if (!qtyRetur || !qtyRetur.value || parseFloat(qtyRetur.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Retur`);
                } else if (parseFloat(qtyRetur.value) > qtyDiterima) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Retur tidak boleh lebih dari Qty Diterima (${qtyDiterima})`);
                }
                if (!idSatuan || !idSatuan.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Satuan`);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi:\n' + emptyFields.join('\n'));
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection



