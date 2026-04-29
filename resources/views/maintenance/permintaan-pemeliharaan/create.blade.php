@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('maintenance.permintaan-pemeliharaan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Permintaan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Permintaan Pemeliharaan</h2>
    </div>
    
    <form action="{{ route('maintenance.permintaan-pemeliharaan.store') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                    Unit Kerja <span class="text-red-500">*</span>
                </label>
                <select 
                    id="id_unit_kerja" 
                    name="id_unit_kerja" 
                    required
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
                <label for="id_pemohon" class="block text-sm font-medium text-gray-700 mb-2">
                    Pemohon <span class="text-red-500">*</span>
                </label>
                <select 
                    id="id_pemohon" 
                    name="id_pemohon" 
                    required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pemohon') border-red-500 @enderror"
                >
                    <option value="">Pilih Pemohon</option>
                    @foreach($pegawais as $pegawai)
                        <option value="{{ $pegawai->id }}" {{ old('id_pemohon') == $pegawai->id ? 'selected' : '' }}>
                            {{ $pegawai->nama_pegawai }}
                        </option>
                    @endforeach
                </select>
                @error('id_pemohon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tanggal_permintaan" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Permintaan <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="tanggal_permintaan" 
                    name="tanggal_permintaan" 
                    required
                    value="{{ old('tanggal_permintaan', date('Y-m-d')) }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_permintaan') border-red-500 @enderror"
                >
                @error('tanggal_permintaan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status_permintaan" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select 
                    id="status_permintaan" 
                    name="status_permintaan" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                    <option value="DRAFT" {{ old('status_permintaan', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="DIAJUKAN" {{ old('status_permintaan') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan (akan masuk ke approval)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Pilih "Diajukan" untuk langsung mengajukan ke approval, atau "Draft" untuk menyimpan sebagai draft</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">
                    Detail Permintaan Aset <span class="text-red-500">*</span>
                </h3>
                <button type="button" id="add-permintaan-row" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                    + Add Row
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 rounded-md border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Register Aset</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Jenis Pemeliharaan</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Prioritas</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Deskripsi Kerusakan</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="permintaan-rows-body"></tbody>
                </table>
            </div>
            @error('rows')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('rows.*.id_register_aset')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('rows.*.jenis_pemeliharaan')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('rows.*.prioritas')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('rows.*.deskripsi_kerusakan')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
            <textarea 
                id="keterangan" 
                name="keterangan" 
                rows="3"
                placeholder="Keterangan tambahan (opsional)..."
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >{{ old('keterangan') }}</textarea>
        </div>

        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('maintenance.permintaan-pemeliharaan.index') }}" 
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const registerAsets = @json($registerAsets->map(function ($aset) {
        return [
            'id' => $aset->id_register_aset,
            'label' => ($aset->nomor_register ?: '-') . ' - ' . ($aset->inventory->dataBarang->nama_barang ?? '-'),
        ];
    })->values()->toArray());
    const oldRows = @json(old('rows', []));
    const tbody = document.getElementById('permintaan-rows-body');
    const addBtn = document.getElementById('add-permintaan-row');

    function registerOptionsHtml(selectedId) {
        let html = '<option value="">Pilih Register Aset</option>';
        registerAsets.forEach(function (aset) {
            const selected = String(selectedId || '') === String(aset.id) ? 'selected' : '';
            html += `<option value="${aset.id}" ${selected}>${aset.label}</option>`;
        });
        return html;
    }

    function addRow(rowData) {
        const index = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.className = 'border-b border-gray-100';
        tr.innerHTML = `
            <td class="px-3 py-2">
                <select name="rows[${index}][id_register_aset]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                    ${registerOptionsHtml(rowData && rowData.id_register_aset)}
                </select>
            </td>
            <td class="px-3 py-2">
                <select name="rows[${index}][jenis_pemeliharaan]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                    <option value="">Pilih Jenis</option>
                    <option value="RUTIN" ${(rowData && rowData.jenis_pemeliharaan === 'RUTIN') ? 'selected' : ''}>Rutin</option>
                    <option value="KALIBRASI" ${(rowData && rowData.jenis_pemeliharaan === 'KALIBRASI') ? 'selected' : ''}>Kalibrasi</option>
                    <option value="PERBAIKAN" ${(rowData && rowData.jenis_pemeliharaan === 'PERBAIKAN') ? 'selected' : ''}>Perbaikan</option>
                    <option value="PENGGANTIAN_SPAREPART" ${(rowData && rowData.jenis_pemeliharaan === 'PENGGANTIAN_SPAREPART') ? 'selected' : ''}>Penggantian Sparepart</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <select name="rows[${index}][prioritas]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                    <option value="">Pilih Prioritas</option>
                    <option value="RENDAH" ${(rowData && rowData.prioritas === 'RENDAH') ? 'selected' : ''}>Rendah</option>
                    <option value="SEDANG" ${(rowData && rowData.prioritas === 'SEDANG') ? 'selected' : ''}>Sedang</option>
                    <option value="TINGGI" ${(rowData && rowData.prioritas === 'TINGGI') ? 'selected' : ''}>Tinggi</option>
                    <option value="DARURAT" ${(rowData && rowData.prioritas === 'DARURAT') ? 'selected' : ''}>Darurat</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <textarea name="rows[${index}][deskripsi_kerusakan]" rows="2" placeholder="Jelaskan kerusakan/masalah..." class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">${(rowData && rowData.deskripsi_kerusakan) ? rowData.deskripsi_kerusakan : ''}</textarea>
            </td>
            <td class="px-3 py-2 text-right">
                <button type="button" class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 hover:bg-red-100" title="Hapus baris" aria-label="Hapus baris">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"></path>
                    </svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

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
@endpush


