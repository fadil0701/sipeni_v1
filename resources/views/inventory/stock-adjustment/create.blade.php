@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('inventory.stock-adjustment.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Stock Adjustment
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Stock Adjustment</h2>
        <p class="mt-1 text-sm text-gray-600">Lakukan penyesuaian stock barang</p>
    </div>
    
    <form action="{{ route('inventory.stock-adjustment.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Detail Adjustment Multi-Row -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Stock Adjustment</h3>
                    <button type="button" id="add-adjustment-row" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                        + Add Row
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="adjustment-rows-table">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Data Stock</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Qty Saat Ini</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Qty Sesudah</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Jenis Adjustment</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="adjustment-rows-body"></tbody>
                    </table>
                </div>
                @error('rows')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('rows.*.id_stock')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('rows.*.qty_sesudah')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('rows.*.jenis_adjustment')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tanggal_adjustment" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Adjustment <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_adjustment" 
                        name="tanggal_adjustment" 
                        value="{{ old('tanggal_adjustment', date('Y-m-d')) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_adjustment') border-red-500 @enderror"
                    >
                    @error('tanggal_adjustment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status" 
                        name="status" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-500 @enderror"
                    >
                        <option value="DRAFT" {{ old('status', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="DIAJUKAN" {{ old('status') == 'DIAJUKAN' ? 'selected' : '' }}>Ajukan</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan
                    </label>
                    <input 
                        type="text" 
                        id="alasan" 
                        name="alasan" 
                        value="{{ old('alasan') }}"
                        placeholder="Alasan adjustment..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('alasan') border-red-500 @enderror"
                    >
                    @error('alasan')
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
                        placeholder="Keterangan tambahan..."
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
                href="{{ route('inventory.stock-adjustment.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Adjustment
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @php
            $stocksPayload = $stocks->map(function ($stock) {
                return [
                    'id' => $stock->id_stock,
                    // Qty Saat Ini ditampilkan di kolom terpisah,
                    // jadi label dropdown jangan ikut membawa angka stock.
                    'label' => ($stock->dataBarang->nama_barang ?? '-') . ' (' . ($stock->gudang->nama_gudang ?? '-') . ')',
                    'qty_akhir' => (float) $stock->qty_akhir,
                ];
            })->values()->toArray();
        @endphp
        const stocks = @json($stocksPayload);
        const oldRows = @json(old('rows', []));
        const tbody = document.getElementById('adjustment-rows-body');
        const addBtn = document.getElementById('add-adjustment-row');

        function stockOptionsHtml(selectedId) {
            let html = '<option value="">Pilih Stock</option>';
            stocks.forEach(function (stock) {
                const selected = String(selectedId || '') === String(stock.id) ? 'selected' : '';
                html += `<option value="${stock.id}" data-qty-akhir="${stock.qty_akhir}" ${selected}>${stock.label}</option>`;
            });
            return html;
        }

        function addRow(rowData) {
            const index = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="rows[${index}][id_stock]" class="row-stock block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                        ${stockOptionsHtml(rowData && rowData.id_stock)}
                    </select>
                </td>
                <td class="px-3 py-2 text-sm text-gray-700">
                    <span class="row-qty-akhir">-</span>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" min="0" name="rows[${index}][qty_sesudah]" value="${(rowData && rowData.qty_sesudah) || ''}" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                </td>
                <td class="px-3 py-2">
                    <select name="rows[${index}][jenis_adjustment]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                        <option value="">Pilih Jenis</option>
                        <option value="PENAMBAHAN" ${(rowData && rowData.jenis_adjustment === 'PENAMBAHAN') ? 'selected' : ''}>Penambahan</option>
                        <option value="PENGURANGAN" ${(rowData && rowData.jenis_adjustment === 'PENGURANGAN') ? 'selected' : ''}>Pengurangan</option>
                        <option value="KOREKSI" ${(rowData && rowData.jenis_adjustment === 'KOREKSI') ? 'selected' : ''}>Koreksi</option>
                        <option value="OPNAME" ${(rowData && rowData.jenis_adjustment === 'OPNAME') ? 'selected' : ''}>Opname</option>
                    </select>
                </td>
                <td class="px-3 py-2 text-right">
                    <button type="button" class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-700" title="Hapus baris" aria-label="Hapus baris">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            const stockSelect = tr.querySelector('.row-stock');
            const qtyAkhirLabel = tr.querySelector('.row-qty-akhir');
            const qtySesudahInput = tr.querySelector('input[name^="rows["][name$="[qty_sesudah]"]');

            function syncStockMeta() {
                const selectedOption = stockSelect.options[stockSelect.selectedIndex];
                // Ambil langsung dari atribut agar kebal terhadap transform camelCase dataset
                const qtyAkhirRaw = selectedOption ? (selectedOption.getAttribute('data-qty-akhir') ?? '0') : '0';
                const qtyAkhir = parseFloat(qtyAkhirRaw);
                if (stockSelect.value) {
                    const safeQty = Number.isFinite(qtyAkhir) ? qtyAkhir : 0;
                    qtyAkhirLabel.textContent = safeQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    if (!qtySesudahInput.value) qtySesudahInput.value = qtyAkhir;
                } else {
                    qtyAkhirLabel.textContent = '-';
                }
            }

            stockSelect.addEventListener('change', syncStockMeta);
            syncStockMeta();

            tr.querySelector('.remove-row').addEventListener('click', function () {
                if (tbody.querySelectorAll('tr').length <= 1) return;
                tr.remove();
            });
        }

        addBtn.addEventListener('click', function () {
            addRow(null);
        });

        if (Array.isArray(oldRows) && oldRows.length > 0) {
            oldRows.forEach(function (row) { addRow(row); });
        } else {
            addRow(null);
        }
    });
</script>
@endsection
