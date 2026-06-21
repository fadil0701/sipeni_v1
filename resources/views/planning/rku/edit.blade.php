@extends('layouts.app')

@section('content')
@php
    $details = old('details') ?? $rku->rkuDetail->map(function ($d) {
        return [
            'id_rku_detail' => $d->id_rku_detail,
            'jenis_rku' => $d->jenis_rku ?? 'BARANG',
            'id_data_barang' => $d->id_data_barang,
            'nama_item' => $d->nama_item ?? ($d->dataBarang?->nama_barang ?? ''),
            'qty_rencana' => $d->qty_rencana,
            'id_satuan' => $d->id_satuan,
            'harga_satuan_rencana' => $d->harga_satuan_rencana,
        ];
    })->toArray();
@endphp

<div class="page-enterprise space-y-4">
    <div class="mb-4">
        <a href="{{ route('planning.rku.show', $rku->id_rku) }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke detail RKU
        </a>
    </div>

    <nav class="mb-1 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('planning.rku.index') }}" class="text-blue-600 hover:underline">RKU</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('planning.rku.show', $rku->id_rku) }}" class="text-blue-600 hover:underline">Detail</a></li>
            <li aria-hidden="true">/</li>
            <li class="font-medium text-gray-800">Ubah</li>
        </ol>
    </nav>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-5">
            <h1 class="text-xl font-semibold text-gray-900">Ubah RKU</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui header dan detail selama status masih dapat diedit.</p>
        </div>

        <form method="POST" action="{{ route('planning.rku.update', $rku->id_rku) }}" id="rkuForm" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert-box alert-error" role="alert">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Periksa kembali isian berikut</p>
                            <ul class="mb-0 mt-2 list-disc pl-5 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="id_unit_kerja" class="mb-2 block text-sm font-medium text-gray-700">
                        Unit Kerja <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="id_unit_kerja"
                        name="id_unit_kerja"
                        required
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('id_unit_kerja') border-red-500 @enderror"
                    >
                        <option value="">Pilih Unit Kerja</option>
                        @foreach ($unitKerjaList ?? [] as $uk)
                            <option value="{{ $uk->id_unit_kerja }}" {{ old('id_unit_kerja', $rku->id_unit_kerja) == $uk->id_unit_kerja ? 'selected' : '' }}>
                                {{ $uk->nama_unit_kerja }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_unit_kerja')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tahun_anggaran" class="mb-2 block text-sm font-medium text-gray-700">
                        Tahun Anggaran <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="tahun_anggaran"
                        name="tahun_anggaran"
                        required
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('tahun_anggaran') border-red-500 @enderror"
                    >
                        @for ($y = date('Y') + 1; $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ old('tahun_anggaran', $rku->tahun_anggaran) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    @error('tahun_anggaran')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea
                    id="keterangan"
                    name="keterangan"
                    rows="3"
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('keterangan') border-red-500 @enderror"
                    placeholder="Catatan tambahan untuk RKU ini (opsional)"
                >{{ old('keterangan', $rku->keterangan) }}</textarea>
                @error('keterangan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">Detail barang / aset</h2>
                    <button
                        type="button"
                        id="addDetailBtn"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah baris
                    </button>
                </div>

                <template id="satuanOptionsTemplate">
                    @foreach ($satuanList ?? [] as $s)
                        <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                    @endforeach
                </template>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200" id="detailsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 14%">Jenis</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 26%">Nama item</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 12%">Qty</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 15%">Satuan</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 18%">Harga satuan</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 18%">Subtotal</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-600" style="width: 2%"></th>
                            </tr>
                        </thead>
                        <tbody id="detailsBody" class="divide-y divide-gray-100 bg-white">
                            @if (count($details) > 0)
                                @foreach ($details as $index => $detail)
                                    <tr class="detail-row">
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="details[{{ $index }}][id_rku_detail]" value="{{ $detail['id_rku_detail'] ?? '' }}">
                                            <select name="details[{{ $index }}][jenis_rku]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                                                <option value="BARANG" {{ ($detail['jenis_rku'] ?? 'BARANG') === 'BARANG' ? 'selected' : '' }}>Barang</option>
                                                <option value="JASA" {{ ($detail['jenis_rku'] ?? '') === 'JASA' ? 'selected' : '' }}>Jasa</option>
                                                <option value="MODAL" {{ in_array($detail['jenis_rku'] ?? '', ['MODAL', 'ASET'], true) ? 'selected' : '' }}>Modal</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="details[{{ $index }}][nama_item]" value="{{ $detail['nama_item'] ?? '' }}" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Contoh: Dental unit / Laptop operasional" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                type="number"
                                                name="details[{{ $index }}][qty_rencana]"
                                                value="{{ $detail['qty_rencana'] ?? '' }}"
                                                step="0.01"
                                                min="0.01"
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-right text-sm"
                                                required
                                            >
                                        </td>
                                        <td class="px-3 py-2">
                                            <select name="details[{{ $index }}][id_satuan]" class="select-satuan block w-full rounded-md border border-gray-300 px-3 py-2 text-sm @error("details.{$index}.id_satuan") border-red-500 @enderror" data-searchable="true" required>
                                                <option value="">Pilih</option>
                                                @foreach ($satuanList ?? [] as $s)
                                                    <option value="{{ $s->id_satuan }}" {{ ($detail['id_satuan'] ?? '') == $s->id_satuan ? 'selected' : '' }}>{{ $s->nama_satuan }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                type="number"
                                                name="details[{{ $index }}][harga_satuan_rencana]"
                                                value="{{ $detail['harga_satuan_rencana'] ?? '' }}"
                                                step="1"
                                                min="0"
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-right text-sm"
                                                placeholder="0"
                                                required
                                            >
                                        </td>
                                        <td class="row-subtotal px-3 py-2 text-right text-sm font-semibold text-gray-800">Rp 0</td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" class="remove-row inline-flex h-9 w-9 items-center justify-center rounded-md border border-red-200 text-red-700 hover:bg-red-50" aria-label="Hapus baris">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7m4 0V4a1 1 0 011-1h4a1 1 0 011 1v3m-8 0h10M10 11v6m4-6v6" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr id="emptyRow">
                                    <td colspan="7" class="px-3 py-10 text-center text-sm text-gray-500">
                                        <svg class="mx-auto mb-2 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4m16 0H4" />
                                        </svg>
                                        <span>Belum ada baris. Klik <strong>Tambah baris</strong> untuk menambahkan item.</span>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-3 py-3 text-right text-sm font-semibold text-gray-700">Total anggaran</td>
                                <td class="px-3 py-3 text-right text-base font-semibold text-blue-600" id="grandTotal">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-5">
                <a href="{{ route('planning.rku.show', $rku->id_rku) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let detailIndex = {{ count($details) }};
    const detailsBody = document.getElementById('detailsBody');
    const emptyRow = document.getElementById('emptyRow');
    const addDetailBtn = document.getElementById('addDetailBtn');
    const grandTotalEl = document.getElementById('grandTotal');
    const satuanOptions = document.getElementById('satuanOptionsTemplate');

    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
    }

    function hitungTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.detail-row').forEach(function(row) {
            const qtyEl = row.querySelector('[name$="[qty_rencana]"]');
            const hargaEl = row.querySelector('[name$="[harga_satuan_rencana]"]');
            const subEl = row.querySelector('.row-subtotal');
            if (!qtyEl || !hargaEl || !subEl) return;
            const qty = parseFloat(qtyEl.value) || 0;
            const harga = parseFloat(hargaEl.value) || 0;
            const subtotal = qty * harga;
            subEl.textContent = formatRupiah(subtotal);
            grandTotal += subtotal;
        });
        if (grandTotalEl) grandTotalEl.textContent = formatRupiah(grandTotal);
    }

    function createDetailRow(index) {
        const tr = document.createElement('tr');
        tr.className = 'detail-row';
        tr.innerHTML = [
            '<td class="px-3 py-2">',
                '<input type="hidden" name="details[' + index + '][id_rku_detail]" value="">',
                '<select name="details[' + index + '][jenis_rku]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>',
                    '<option value="BARANG">Barang</option>',
                    '<option value="JASA">Jasa</option>',
                    '<option value="MODAL">Modal</option>',
                '</select>',
            '</td>',
            '<td class="px-3 py-2">',
                '<input type="text" name="details[' + index + '][nama_item]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Contoh: Dental unit / Laptop operasional" required>',
            '</td>',
            '<td class="px-3 py-2">',
                '<input type="number" name="details[' + index + '][qty_rencana]" step="0.01" min="0.01" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-right text-sm" required>',
            '</td>',
            '<td class="px-3 py-2">',
                '<select name="details[' + index + '][id_satuan]" class="select-satuan block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" data-searchable="true" required>',
                    '<option value="">Pilih</option>',
                '</select>',
            '</td>',
            '<td class="px-3 py-2">',
                '<input type="number" name="details[' + index + '][harga_satuan_rencana]" step="1" min="0" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-right text-sm" placeholder="0" required>',
            '</td>',
            '<td class="row-subtotal px-3 py-2 text-right text-sm font-semibold text-gray-800">Rp 0</td>',
            '<td class="px-3 py-2 text-center">',
                '<button type="button" class="remove-row inline-flex h-9 w-9 items-center justify-center rounded-md border border-red-200 text-red-700 hover:bg-red-50" aria-label="Hapus baris"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7m4 0V4a1 1 0 011-1h4a1 1 0 011 1v3m-8 0h10M10 11v6m4-6v6"></path></svg></button>',
            '</td>',
        ].join('');

        const satuanSelect = tr.querySelector('select[name="details[' + index + '][id_satuan]"]');
        if (satuanSelect && satuanOptions) {
            satuanSelect.innerHTML = '<option value="">Pilih</option>' + satuanOptions.innerHTML;
        }

        tr.querySelectorAll('input, select').forEach(function(el) {
            el.addEventListener('input', hitungTotal);
            el.addEventListener('change', hitungTotal);
        });

        if (satuanSelect && window.initChoicesForSelect) {
            window.initChoicesForSelect(satuanSelect, 1);
        }

        return tr;
    }

    if (addDetailBtn) {
        addDetailBtn.addEventListener('click', function() {
            if (emptyRow) emptyRow.style.display = 'none';
            const tr = createDetailRow(detailIndex);
            detailsBody.appendChild(tr);
            detailIndex++;
            hitungTotal();
        });
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) {
            row.remove();
            if (document.querySelectorAll('.detail-row').length === 0) {
                if (emptyRow) emptyRow.style.display = '';
            }
            hitungTotal();
        }
    });

    document.querySelectorAll('.detail-row input, .detail-row select').forEach(function(el) {
        el.addEventListener('input', hitungTotal);
        el.addEventListener('change', hitungTotal);
    });
    hitungTotal();
});
</script>
@endpush
