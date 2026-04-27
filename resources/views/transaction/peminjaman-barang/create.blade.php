@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.peminjaman-barang.index') }}" class="btn-secondary-ui inline-flex items-center bg-red-500 text-white">
        <svg class="mr-1 h-4 w-4" fill="none" stroke="white" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar
    </a>
</div>

<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-5">
        <h2 class="text-xl font-semibold text-gray-900">Ajukan Peminjaman</h2>
    </div>

    <form action="{{ route('transaction.peminjaman-barang.store') }}" method="POST" class="p-6 space-y-6">
        @csrf

        @if($errors->any())
            <div class="alert-box alert-error">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            @if(!empty($isAdminWithoutPegawai))
                <div>
                    <label for="id_pemohon_manual" class="mb-2 block text-sm font-medium text-gray-700">Pemohon Pegawai <span class="text-red-500">*</span></label>
                    <select id="id_pemohon_manual" name="id_pemohon_manual" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Pemohon Pegawai</option>
                        @foreach($pemohonPegawaiOptions as $optPegawai)
                            <option value="{{ $optPegawai->id }}" {{ (string) old('id_pemohon_manual') === (string) $optPegawai->id ? 'selected' : '' }}>
                                {{ $optPegawai->nama_pegawai }} - {{ $optPegawai->unitKerja->nama_unit_kerja ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="id_unit_peminjam" class="mb-2 block text-sm font-medium text-gray-700">Unit Peminjam <span class="text-red-500">*</span></label>
                    <select id="id_unit_peminjam" name="id_unit_peminjam" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Unit Peminjam</option>
                        @foreach($unitPeminjamOptions as $unit)
                            <option value="{{ $unit->id_unit_kerja }}" {{ (string) old('id_unit_peminjam') === (string) $unit->id_unit_kerja ? 'selected' : '' }}>
                                {{ $unit->nama_unit_kerja }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Unit Peminjam</label>
                    <input type="text" value="{{ $pegawai->unitKerja->nama_unit_kerja ?? '-' }}" readonly class="block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm">
                </div>
            @endif

            <div>
                <label for="tujuan_peminjaman" class="mb-2 block text-sm font-medium text-gray-700">Tujuan Peminjaman <span class="text-red-500">*</span></label>
                <select id="tujuan_peminjaman" name="tujuan_peminjaman" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih Tujuan</option>
                    <option value="UNIT" {{ old('tujuan_peminjaman') === 'UNIT' ? 'selected' : '' }}>Antar Unit Kerja (Unit Pemilik)</option>
                    <option value="GUDANG_PUSAT" {{ old('tujuan_peminjaman') === 'GUDANG_PUSAT' ? 'selected' : '' }}>Gudang Pusat (Pengurus Barang)</option>
                </select>
            </div>

            <div id="field_unit_pemilik" style="display: none;">
                <label for="id_unit_pemilik" class="mb-2 block text-sm font-medium text-gray-700">Unit Pemilik</label>
                <select id="id_unit_pemilik" name="id_unit_pemilik" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih Unit Pemilik</option>
                    @foreach($unitPemilikList as $unit)
                        <option value="{{ $unit->id_unit_kerja }}" {{ (string) old('id_unit_pemilik') === (string) $unit->id_unit_kerja ? 'selected' : '' }}>
                            {{ $unit->nama_unit_kerja }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="field_gudang_pusat" style="display: none;">
                <label for="id_gudang_pusat" class="mb-2 block text-sm font-medium text-gray-700">Gudang Pusat</label>
                <select id="id_gudang_pusat" name="id_gudang_pusat" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih Gudang Pusat</option>
                    @foreach($gudangPusatList as $gudang)
                        <option value="{{ $gudang->id_gudang }}" {{ (string) old('id_gudang_pusat') === (string) $gudang->id_gudang ? 'selected' : '' }}>
                            {{ $gudang->nama_gudang }} - {{ $gudang->kategori_gudang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tanggal_pinjam" class="mb-2 block text-sm font-medium text-gray-700">Tanggal Pinjam <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', now()->toDateString()) }}" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="tanggal_rencana_kembali" class="mb-2 block text-sm font-medium text-gray-700">Rencana Tanggal Kembali <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_rencana_kembali" name="tanggal_rencana_kembali" value="{{ old('tanggal_rencana_kembali') }}" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        @php
            $oldItems = old('items', [['id_data_barang' => '', 'id_satuan' => '', 'qty_pinjam' => '', 'keterangan_detail' => '']]);
            if (!is_array($oldItems) || count($oldItems) === 0) {
                $oldItems = [['id_data_barang' => '', 'id_satuan' => '', 'qty_pinjam' => '', 'keterangan_detail' => '']];
            }
        @endphp
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Data Barang Dipinjam</h3>
                <button type="button" id="add-item-row" class="btn-primary-ui">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed divide-y divide-gray-200 rounded-md border border-gray-200">
                    <colgroup>
                        <col style="width: 42%;">
                        <col style="width: 20%;">
                        <col style="width: 14%;">
                        <col style="width: 18%;">
                        <col style="width: 6%;">
                    </colgroup>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Barang</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Satuan</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Qty</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Keterangan</th>
                            <th class="px-2 py-2 text-center text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="item-rows" class="divide-y divide-gray-200 bg-white">
                        @foreach($oldItems as $idx => $oldItem)
                            <tr class="item-row">
                                <td class="px-3 py-2 align-top">
                                    <select name="items[{{ $idx }}][id_data_barang]" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih Barang</option>
                                        @foreach($dataBarangs as $barang)
                                            <option value="{{ $barang->id_data_barang }}" {{ (string) ($oldItem['id_data_barang'] ?? '') === (string) $barang->id_data_barang ? 'selected' : '' }}>
                                                {{ $barang->kode_data_barang }} - {{ $barang->nama_barang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <select name="items[{{ $idx }}][id_satuan]" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih Satuan</option>
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->id_satuan }}" {{ (string) ($oldItem['id_satuan'] ?? '') === (string) $satuan->id_satuan ? 'selected' : '' }}>
                                                {{ $satuan->nama_satuan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][qty_pinjam]" value="{{ $oldItem['qty_pinjam'] ?? '' }}" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input type="text" name="items[{{ $idx }}][keterangan_detail]" value="{{ $oldItem['keterangan_detail'] ?? '' }}" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Opsional">
                                </td>
                                <td class="w-[56px] px-2 py-2 align-middle text-center">
                                    <button type="button" class="remove-item-row inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-300 text-red-800 hover:bg-red-50" title="Hapus baris" aria-label="Hapus baris">
                                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>   
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-gray-500">Tambahkan lebih dari satu barang sesuai kebutuhan peminjaman.</p>
        </div>

        <div>
            <label for="alasan" class="mb-2 block text-sm font-medium text-gray-700">Alasan Peminjaman <span class="text-red-500">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ old('alasan') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
            <a href="{{ route('transaction.peminjaman-barang.index') }}" class="btn-secondary-ui">Batal</a>
            <button type="submit" class="btn-primary-ui">Ajukan Permintaan</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tujuan = document.getElementById('tujuan_peminjaman');
    const fieldUnit = document.getElementById('field_unit_pemilik');
    const fieldGudang = document.getElementById('field_gudang_pusat');
    const unitSelect = document.getElementById('id_unit_pemilik');
    const gudangSelect = document.getElementById('id_gudang_pusat');

    function toggleTujuanFields() {
        if (tujuan.value === 'UNIT') {
            fieldUnit.style.display = 'block';
            fieldGudang.style.display = 'none';
            unitSelect.setAttribute('required', 'required');
            gudangSelect.removeAttribute('required');
            gudangSelect.value = '';
        } else if (tujuan.value === 'GUDANG_PUSAT') {
            fieldGudang.style.display = 'block';
            fieldUnit.style.display = 'none';
            gudangSelect.setAttribute('required', 'required');
            unitSelect.removeAttribute('required');
            unitSelect.value = '';
        } else {
            fieldUnit.style.display = 'none';
            fieldGudang.style.display = 'none';
            unitSelect.removeAttribute('required');
            gudangSelect.removeAttribute('required');
            unitSelect.value = '';
            gudangSelect.value = '';
        }
    }

    toggleTujuanFields();
    tujuan.addEventListener('change', toggleTujuanFields);
    if (window.jQuery) {
        window.jQuery(tujuan).on('select2:select select2:clear', toggleTujuanFields);
    }

    const itemRows = document.getElementById('item-rows');
    const addItemRowBtn = document.getElementById('add-item-row');

    function renderBarangOptions() {
        return `
            <option value="">Pilih Barang</option>
            @foreach($dataBarangs as $barang)
                <option value="{{ $barang->id_data_barang }}">{{ $barang->kode_data_barang }} - {{ $barang->nama_barang }}</option>
            @endforeach
        `;
    }

    function renderSatuanOptions() {
        return `
            <option value="">Pilih Satuan</option>
            @foreach($satuans as $satuan)
                <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
            @endforeach
        `;
    }

    function reindexRows() {
        const rows = itemRows.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            row.querySelectorAll('select, input').forEach((field) => {
                const name = field.getAttribute('name');
                if (!name) return;
                field.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
            });
        });
    }

    function attachRemoveHandlers() {
        itemRows.querySelectorAll('.remove-item-row').forEach((btn) => {
            btn.onclick = function () {
                const rows = itemRows.querySelectorAll('.item-row');
                if (rows.length <= 1) {
                    return;
                }
                btn.closest('.item-row')?.remove();
                reindexRows();
            };
        });
    }

    if (addItemRowBtn && itemRows) {
        addItemRowBtn.addEventListener('click', function () {
            const index = itemRows.querySelectorAll('.item-row').length;
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                <td class="px-3 py-2 align-top">
                    <select name="items[${index}][id_data_barang]" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">${renderBarangOptions()}</select>
                </td>
                <td class="px-3 py-2 align-top">
                    <select name="items[${index}][id_satuan]" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">${renderSatuanOptions()}</select>
                </td>
                <td class="px-3 py-2 align-top">
                    <input type="number" step="0.01" min="0.01" name="items[${index}][qty_pinjam]" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </td>
                <td class="px-3 py-2 align-top">
                    <input type="text" name="items[${index}][keterangan_detail]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Opsional">
                </td>
                <td class="w-[56px] px-2 py-2 align-middle text-center">
                    <button type="button" class="remove-item-row inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-300 text-red-700 hover:bg-red-50" title="Hapus baris" aria-label="Hapus baris">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>
            `;
            itemRows.appendChild(tr);
            attachRemoveHandlers();
        });
        attachRemoveHandlers();
    }
});
</script>
@endsection

