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
    
    <form action="{{ route('maintenance.permintaan-pemeliharaan.store') }}" method="POST" class="p-6 space-y-6" enctype="multipart/form-data">
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
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase min-w-[14rem]">Register Aset</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Merk</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Tipe</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">No Seri</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Jenis Pemeliharaan</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Prioritas</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Deskripsi Kerusakan</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Foto Kondisi</th>
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
            @error('rows.*.foto_kondisi')
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
    const registerAsets = @json($registerAsetOptions);
    const registerById = {};
    registerAsets.forEach(function (aset) {
        registerById[String(aset.id)] = aset;
    });
    const oldRows = @json(old('rows', []));
    const tbody = document.getElementById('permintaan-rows-body');
    const addBtn = document.getElementById('add-permintaan-row');

    function escapeHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function registerOptionsHtml(selectedId) {
        let html = '<option value="">Pilih Register Aset</option>';
        registerAsets.forEach(function (aset) {
            const selected = String(selectedId || '') === String(aset.id) ? 'selected' : '';
            html += '<option value="' + aset.id + '" ' + selected + '>' + escapeHtml(aset.label) + '</option>';
        });
        return html;
    }

    function refreshSearchableSelect(selectElement) {
        if (!selectElement || selectElement.tagName !== 'SELECT') return;
        if (!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function')) {
            return;
        }
        const $el = window.jQuery(selectElement);
        if ($el.hasClass('select2-hidden-accessible')) {
            try { $el.select2('destroy'); } catch (e) {}
        }
        delete selectElement.dataset.sipeniSelect2Init;
        selectElement.classList.add('select-searchable', 'js-example-placeholder-single');
        selectElement.setAttribute('data-searchable', 'true');
        $el.select2(Object.assign({}, (typeof window.sipeniSelect2BaseOptions === 'function' ? window.sipeniSelect2BaseOptions() : { width: '100%', minimumResultsForSearch: 0 }), {
            width: '100%',
            placeholder: 'Cari register / nama barang...',
            allowClear: true,
            dropdownParent: window.jQuery(selectElement.closest('form') || document.body),
        }));
    }

    function fillAsetMeta(tr, registerId) {
        const aset = registerById[String(registerId || '')] || null;
        tr.querySelector('.meta-merk').textContent = aset ? (aset.merk || '-') : '-';
        tr.querySelector('.meta-tipe').textContent = aset ? (aset.tipe || '-') : '-';
        tr.querySelector('.meta-no-seri').textContent = aset ? (aset.no_seri || '-') : '-';
    }

    function reindexRows() {
        tbody.querySelectorAll('tr').forEach(function (tr, index) {
            tr.querySelectorAll('[name^="rows["]').forEach(function (el) {
                el.name = el.name.replace(/rows\[\d+]/, 'rows[' + index + ']');
            });
        });
    }

    function addRow(rowData) {
        const index = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.className = 'border-b border-gray-100';
        const selectedRegisterId = rowData && rowData.id_register_aset ? rowData.id_register_aset : '';
        const deskripsi = rowData && rowData.deskripsi_kerusakan ? escapeHtml(rowData.deskripsi_kerusakan) : '';
        const jenis = rowData && rowData.jenis_pemeliharaan ? rowData.jenis_pemeliharaan : '';
        const prioritas = rowData && rowData.prioritas ? rowData.prioritas : '';

        tr.innerHTML =
            '<td class="px-3 py-2" style="min-width:14rem">' +
                '<select name="rows[' + index + '][id_register_aset]" class="select-register-aset select-searchable block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" data-searchable="true" required>' +
                    registerOptionsHtml(selectedRegisterId) +
                '</select>' +
            '</td>' +
            '<td class="px-3 py-2 whitespace-nowrap"><span class="meta-merk text-sm text-gray-800">-</span></td>' +
            '<td class="px-3 py-2 whitespace-nowrap"><span class="meta-tipe text-sm text-gray-800">-</span></td>' +
            '<td class="px-3 py-2 whitespace-nowrap"><span class="meta-no-seri text-sm text-gray-800">-</span></td>' +
            '<td class="px-3 py-2">' +
                '<select name="rows[' + index + '][jenis_pemeliharaan]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>' +
                    '<option value="">Pilih Jenis</option>' +
                    '<option value="RUTIN"' + (jenis === 'RUTIN' ? ' selected' : '') + '>Rutin</option>' +
                    '<option value="KALIBRASI"' + (jenis === 'KALIBRASI' ? ' selected' : '') + '>Kalibrasi</option>' +
                    '<option value="PERBAIKAN"' + (jenis === 'PERBAIKAN' ? ' selected' : '') + '>Perbaikan</option>' +
                    '<option value="PENGGANTIAN_SPAREPART"' + (jenis === 'PENGGANTIAN_SPAREPART' ? ' selected' : '') + '>Penggantian Sparepart</option>' +
                '</select>' +
            '</td>' +
            '<td class="px-3 py-2">' +
                '<select name="rows[' + index + '][prioritas]" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>' +
                    '<option value="">Pilih Prioritas</option>' +
                    '<option value="RENDAH"' + (prioritas === 'RENDAH' ? ' selected' : '') + '>Rendah</option>' +
                    '<option value="SEDANG"' + (prioritas === 'SEDANG' ? ' selected' : '') + '>Sedang</option>' +
                    '<option value="TINGGI"' + (prioritas === 'TINGGI' ? ' selected' : '') + '>Tinggi</option>' +
                    '<option value="DARURAT"' + (prioritas === 'DARURAT' ? ' selected' : '') + '>Darurat</option>' +
                '</select>' +
            '</td>' +
            '<td class="px-3 py-2">' +
                '<textarea name="rows[' + index + '][deskripsi_kerusakan]" rows="2" placeholder="Jelaskan kerusakan/masalah..." class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">' + deskripsi + '</textarea>' +
            '</td>' +
            '<td class="px-3 py-2">' +
                '<div class="space-y-2" style="min-width:10rem">' +
                    '<input type="file" name="rows[' + index + '][foto_kondisi]" accept="image/jpeg,image/png,image/jpg,image/webp" class="foto-kondisi-input block w-full text-xs text-gray-600 file:mr-2 file:rounded-md file:border-0 file:bg-blue-50 file:px-2 file:py-1.5 file:text-xs file:font-medium file:text-blue-700 hover:file:bg-blue-100">' +
                    '<img src="" alt="Preview foto kondisi" class="foto-kondisi-preview hidden h-16 w-16 rounded-md border border-gray-200 object-cover">' +
                    '<p class="text-[11px] text-gray-500">JPG/PNG/WebP, maks. 5 MB</p>' +
                '</div>' +
            '</td>' +
            '<td class="px-3 py-2 text-right">' +
                '<button type="button" class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 hover:bg-red-100" title="Hapus baris" aria-label="Hapus baris">' +
                    '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7"></path>' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"></path>' +
                    '</svg>' +
                '</button>' +
            '</td>';

        tbody.appendChild(tr);

        const registerSelect = tr.querySelector('.select-register-aset');
        refreshSearchableSelect(registerSelect);
        fillAsetMeta(tr, selectedRegisterId);

        const onRegisterChange = function () {
            fillAsetMeta(tr, registerSelect.value);
        };
        if (window.jQuery) {
            window.jQuery(registerSelect).on('change select2:select select2:clear', onRegisterChange);
        } else {
            registerSelect.addEventListener('change', onRegisterChange);
        }

        const fotoInput = tr.querySelector('.foto-kondisi-input');
        const fotoPreview = tr.querySelector('.foto-kondisi-preview');
        if (fotoInput && fotoPreview) {
            fotoInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) {
                    fotoPreview.src = '';
                    fotoPreview.classList.add('hidden');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    fotoPreview.src = e.target.result;
                    fotoPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            });
        }

        tr.querySelector('.remove-row').addEventListener('click', function () {
            if (tbody.querySelectorAll('tr').length <= 1) return;
            if (window.jQuery && window.jQuery(registerSelect).hasClass('select2-hidden-accessible')) {
                try { window.jQuery(registerSelect).select2('destroy'); } catch (e) {}
            }
            tr.remove();
            reindexRows();
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
