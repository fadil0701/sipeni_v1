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
        <h2 class="text-xl font-semibold text-gray-900">Edit Permintaan Pemeliharaan</h2>
        <p class="text-sm text-gray-600 mt-1">No. Permintaan: <span class="font-semibold">{{ $permintaan->no_permintaan_pemeliharaan }}</span></p>
    </div>
    
    <form action="{{ route('maintenance.permintaan-pemeliharaan.update', $permintaan->id_permintaan_pemeliharaan) }}" method="POST" class="p-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
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
                            <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', $permintaan->id_unit_kerja) == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
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
                            <option value="{{ $pegawai->id }}" {{ old('id_pemohon', $permintaan->id_pemohon) == $pegawai->id ? 'selected' : '' }}>
                                {{ $pegawai->nama_pegawai }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_pemohon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="id_register_aset" class="block text-sm font-medium text-gray-700 mb-2">
                    Register Aset <span class="text-red-500">*</span>
                </label>
                <select 
                    id="id_register_aset" 
                    name="id_register_aset" 
                    required
                    data-searchable="true"
                    class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_register_aset') border-red-500 @enderror"
                >
                    <option value="">Pilih Register Aset</option>
                    @foreach($registerAsets as $aset)
                        @php
                            $inv = $aset->inventory;
                            $noSeri = $aset->inventoryItem->no_seri ?? ($inv->no_seri ?? '-');
                        @endphp
                        <option
                            value="{{ $aset->id_register_aset }}"
                            data-merk="{{ $inv->merk ?? '-' }}"
                            data-tipe="{{ $inv->tipe ?? '-' }}"
                            data-no-seri="{{ $noSeri }}"
                            {{ old('id_register_aset', $permintaan->id_register_aset) == $aset->id_register_aset ? 'selected' : '' }}
                        >
                            {{ $aset->nomor_register }} - {{ $inv->dataBarang->nama_barang ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('id_register_aset')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Merk</label>
                    <input type="text" id="meta_merk" readonly class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-md text-sm text-gray-800" value="-">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                    <input type="text" id="meta_tipe" readonly class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-md text-sm text-gray-800" value="-">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No Seri</label>
                    <input type="text" id="meta_no_seri" readonly class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-md text-sm text-gray-800" value="-">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label for="tanggal_permintaan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Permintaan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_permintaan" 
                        name="tanggal_permintaan" 
                        required
                        value="{{ old('tanggal_permintaan', $permintaan->tanggal_permintaan->format('Y-m-d')) }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_permintaan') border-red-500 @enderror"
                    >
                    @error('tanggal_permintaan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jenis_pemeliharaan" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Pemeliharaan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="jenis_pemeliharaan" 
                        name="jenis_pemeliharaan" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_pemeliharaan') border-red-500 @enderror"
                    >
                        <option value="">Pilih Jenis</option>
                        <option value="RUTIN" {{ old('jenis_pemeliharaan', $permintaan->jenis_pemeliharaan) == 'RUTIN' ? 'selected' : '' }}>Rutin</option>
                        <option value="KALIBRASI" {{ old('jenis_pemeliharaan', $permintaan->jenis_pemeliharaan) == 'KALIBRASI' ? 'selected' : '' }}>Kalibrasi</option>
                        <option value="PERBAIKAN" {{ old('jenis_pemeliharaan', $permintaan->jenis_pemeliharaan) == 'PERBAIKAN' ? 'selected' : '' }}>Perbaikan</option>
                        <option value="PENGGANTIAN_SPAREPART" {{ old('jenis_pemeliharaan', $permintaan->jenis_pemeliharaan) == 'PENGGANTIAN_SPAREPART' ? 'selected' : '' }}>Penggantian Sparepart</option>
                    </select>
                    @error('jenis_pemeliharaan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="prioritas" class="block text-sm font-medium text-gray-700 mb-2">
                        Prioritas <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="prioritas" 
                        name="prioritas" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('prioritas') border-red-500 @enderror"
                    >
                        <option value="">Pilih Prioritas</option>
                        <option value="RENDAH" {{ old('prioritas', $permintaan->prioritas) == 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                        <option value="SEDANG" {{ old('prioritas', $permintaan->prioritas) == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                        <option value="TINGGI" {{ old('prioritas', $permintaan->prioritas) == 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                        <option value="DARURAT" {{ old('prioritas', $permintaan->prioritas) == 'DARURAT' ? 'selected' : '' }}>Darurat</option>
                    </select>
                    @error('prioritas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="deskripsi_kerusakan" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi Kerusakan / Masalah
                </label>
                <textarea 
                    id="deskripsi_kerusakan" 
                    name="deskripsi_kerusakan" 
                    rows="4"
                    placeholder="Jelaskan kerusakan atau masalah yang terjadi pada aset..."
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('deskripsi_kerusakan') border-red-500 @enderror"
                >{{ old('deskripsi_kerusakan', $permintaan->deskripsi_kerusakan) }}</textarea>
                @error('deskripsi_kerusakan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="foto_kondisi" class="block text-sm font-medium text-gray-700 mb-2">
                    Foto Kondisi Barang
                </label>
                @if($permintaan->foto_kondisi)
                    <div class="mb-3 flex items-start gap-4">
                        <img
                            src="{{ route('media.show', ['path' => $permintaan->foto_kondisi]) }}"
                            alt="Foto kondisi saat ini"
                            class="h-28 w-28 rounded-md border border-gray-200 object-cover"
                        >
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="hapus_foto_kondisi" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            Hapus foto saat ini
                        </label>
                    </div>
                @endif
                <input
                    type="file"
                    id="foto_kondisi"
                    name="foto_kondisi"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 @error('foto_kondisi') border-red-500 @enderror"
                    onchange="previewFotoKondisi(this)"
                >
                <img id="foto_kondisi_preview" src="" alt="Preview foto kondisi" class="mt-3 hidden h-28 w-28 rounded-md border border-gray-200 object-cover">
                <p class="mt-1 text-xs text-gray-500">Opsional. Format JPG/PNG/WebP, maksimal 5 MB.</p>
                @error('foto_kondisi')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                >{{ old('keterangan', $permintaan->keterangan) }}</textarea>
            </div>

            <div>
                <label for="status_permintaan" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select 
                    id="status_permintaan" 
                    name="status_permintaan" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                    <option value="DRAFT" {{ old('status_permintaan', $permintaan->status_permintaan) == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="DIAJUKAN" {{ old('status_permintaan', $permintaan->status_permintaan) == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan (akan masuk ke approval)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Pilih "Diajukan" untuk langsung mengajukan ke approval, atau "Draft" untuk menyimpan sebagai draft</p>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
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
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewFotoKondisi(input) {
    const preview = document.getElementById('foto_kondisi_preview');
    if (!preview) return;
    const file = input.files && input.files[0];
    if (!file) {
        preview.src = '';
        preview.classList.add('hidden');
        return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('id_register_aset');
    const merk = document.getElementById('meta_merk');
    const tipe = document.getElementById('meta_tipe');
    const noSeri = document.getElementById('meta_no_seri');

    function syncMeta() {
        if (!select) return;
        const option = select.options[select.selectedIndex];
        if (merk) merk.value = option?.dataset?.merk || '-';
        if (tipe) tipe.value = option?.dataset?.tipe || '-';
        if (noSeri) noSeri.value = option?.dataset?.noSeri || '-';
    }

    if (select) {
        if (window.jQuery) {
            window.jQuery(select).on('change select2:select select2:clear', syncMeta);
        } else {
            select.addEventListener('change', syncMeta);
        }
        syncMeta();
    }
});
</script>
@endpush


