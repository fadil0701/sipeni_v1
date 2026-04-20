@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('inventory.data-inventory.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Data Inventory
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Data Inventory</h2>
    </div>
    
    <form action="{{ route('inventory.data-inventory.update', $dataInventory->id_inventory) }}" method="POST" class="p-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Barang -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Barang</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_data_barang" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Barang <span class="text-red-500">*</span>
                        </label>
                        <select id="id_data_barang" name="id_data_barang" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_data_barang') border-red-500 @enderror">
                            <option value="">Pilih Data Barang</option>
                            @foreach($dataBarangs as $dataBarang)
                                <option value="{{ $dataBarang->id_data_barang }}" {{ old('id_data_barang', $dataInventory->id_data_barang) == $dataBarang->id_data_barang ? 'selected' : '' }}>
                                    {{ $dataBarang->kode_data_barang }} - {{ $dataBarang->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_data_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="id_gudang" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang <span class="text-red-500">*</span>
                        </label>
                        <select id="id_gudang" name="id_gudang" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang') border-red-500 @enderror">
                            <option value="">Pilih Gudang Pusat</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang', $dataInventory->id_gudang) == $gudang->id_gudang ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }}
                                    @if($gudang->kategori_gudang)
                                        - {{ $gudang->kategori_gudang }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Hanya gudang PUSAT yang dapat digunakan untuk input inventory. Gudang UNIT hanya menerima distribusi barang.</p>
                        @error('id_gudang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="id_anggaran" class="block text-sm font-medium text-gray-700 mb-2">
                            Sumber Anggaran <span class="text-red-500">*</span>
                        </label>
                        <select id="id_anggaran" name="id_anggaran" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_anggaran') border-red-500 @enderror">
                            <option value="">Pilih Sumber Anggaran</option>
                            @foreach($sumberAnggarans as $sumberAnggaran)
                                <option value="{{ $sumberAnggaran->id_anggaran }}" {{ old('id_anggaran', $dataInventory->id_anggaran) == $sumberAnggaran->id_anggaran ? 'selected' : '' }}>
                                    {{ $sumberAnggaran->nama_anggaran }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_anggaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="id_sub_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                            Sub Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <select id="id_sub_kegiatan" name="id_sub_kegiatan" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_sub_kegiatan') border-red-500 @enderror">
                            <option value="">Pilih Sub Kegiatan</option>
                            @foreach($subKegiatans as $subKegiatan)
                                <option value="{{ $subKegiatan->id_sub_kegiatan }}" {{ old('id_sub_kegiatan', $dataInventory->id_sub_kegiatan) == $subKegiatan->id_sub_kegiatan ? 'selected' : '' }}>
                                    {{ $subKegiatan->kode_sub_kegiatan }} - {{ $subKegiatan->nama_sub_kegiatan }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sub_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="jenis_inventory" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Inventory <span class="text-red-500">*</span>
                        </label>
                        <select id="jenis_inventory" name="jenis_inventory" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_inventory') border-red-500 @enderror">
                            <option value="">Pilih Jenis</option>
                            <option value="ASET" {{ old('jenis_inventory', $dataInventory->jenis_inventory) == 'ASET' ? 'selected' : '' }}>ASET</option>
                            <option value="PERSEDIAAN" {{ old('jenis_inventory', $dataInventory->jenis_inventory) == 'PERSEDIAAN' ? 'selected' : '' }}>PERSEDIAAN</option>
                            <option value="FARMASI" {{ old('jenis_inventory', $dataInventory->jenis_inventory) == 'FARMASI' ? 'selected' : '' }}>FARMASI</option>
                        </select>
                        @error('jenis_inventory')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div id="jenis_barang_field" style="display: {{ in_array(old('jenis_inventory', $dataInventory->jenis_inventory), ['ASET','PERSEDIAAN','FARMASI']) ? 'block' : 'none' }};">
                        <label for="jenis_barang" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Barang <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="jenis_barang" 
                            name="jenis_barang" 
                            data-current-value="{{ old('jenis_barang', $dataInventory->jenis_barang) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_barang') border-red-500 @enderror"
                        >
                            <option value="">Pilih Jenis Barang</option>
                        </select>
                        @error('jenis_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="status_inventory" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Inventory <span class="text-red-500">*</span>
                        </label>
                        <select id="status_inventory" name="status_inventory" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_inventory') border-red-500 @enderror">
                            <option value="">Pilih Status</option>
                            <option value="DRAFT" {{ old('status_inventory', $dataInventory->status_inventory) == 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                            <option value="AKTIF" {{ old('status_inventory', $dataInventory->status_inventory) == 'AKTIF' ? 'selected' : '' }}>AKTIF</option>
                            <option value="DISTRIBUSI" {{ old('status_inventory', $dataInventory->status_inventory) == 'DISTRIBUSI' ? 'selected' : '' }}>DISTRIBUSI</option>
                            <option value="HABIS" {{ old('status_inventory', $dataInventory->status_inventory) == 'HABIS' ? 'selected' : '' }}>HABIS</option>
                        </select>
                        @error('status_inventory')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Kuantitas & Harga -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kuantitas & Harga</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tahun_anggaran" class="block text-sm font-medium text-gray-700 mb-2">
                            Tahun Anggaran <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="tahun_anggaran" name="tahun_anggaran" required min="1900" max="{{ date('Y') + 5 }}" value="{{ old('tahun_anggaran', $dataInventory->tahun_anggaran) }}" placeholder="Tahun anggaran" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tahun_anggaran') border-red-500 @enderror">
                        @error('tahun_anggaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="qty_input" class="block text-sm font-medium text-gray-700 mb-2">
                            Qty Input <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="qty_input" name="qty_input" required min="1" step="0.01" value="{{ old('qty_input', $dataInventory->qty_input) }}" placeholder="Masukkan jumlah" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('qty_input') border-red-500 @enderror">
                        @error('qty_input')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="id_satuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Satuan <span class="text-red-500">*</span>
                        </label>
                        <select id="id_satuan" name="id_satuan" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_satuan') border-red-500 @enderror">
                            <option value="">Pilih Satuan</option>
                            @foreach($satuans as $satuan)
                                <option value="{{ $satuan->id_satuan }}" {{ old('id_satuan', $dataInventory->id_satuan) == $satuan->id_satuan ? 'selected' : '' }}>{{ $satuan->nama_satuan }}</option>
                            @endforeach
                        </select>
                        @error('id_satuan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="harga_satuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Harga Satuan <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="harga_satuan" name="harga_satuan" required min="0" step="0.01" value="{{ old('harga_satuan', $dataInventory->harga_satuan) }}" placeholder="Masukkan harga satuan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('harga_satuan') border-red-500 @enderror">
                        @error('harga_satuan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Informasi Teknis -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Teknis</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="merk" class="block text-sm font-medium text-gray-700 mb-2">Merk</label>
                        <input type="text" id="merk" name="merk" value="{{ old('merk', $dataInventory->merk) }}" placeholder="Masukkan merk" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div id="tipe_field" style="display: {{ old('jenis_inventory', $dataInventory->jenis_inventory) == 'ASET' ? 'block' : 'none' }};">
                        <label for="tipe" class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                        <input type="text" id="tipe" name="tipe" value="{{ old('tipe', $dataInventory->tipe) }}" placeholder="Masukkan tipe" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="tahun_produksi" class="block text-sm font-medium text-gray-700 mb-2">Tahun Produksi</label>
                        <input type="number" id="tahun_produksi" name="tahun_produksi" min="1900" max="{{ date('Y') + 5 }}" value="{{ old('tahun_produksi', $dataInventory->tahun_produksi) }}" placeholder="Tahun produksi" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="nama_penyedia" class="block text-sm font-medium text-gray-700 mb-2">Nama Penyedia</label>
                        <input 
                            type="text" 
                            id="nama_penyedia" 
                            name="nama_penyedia" 
                            value="{{ old('nama_penyedia', $dataInventory->nama_penyedia) }}"
                            placeholder="Masukkan nama penyedia"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_penyedia') border-red-500 @enderror"
                        >
                        @error('nama_penyedia')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="no_seri_field" style="display: {{ old('jenis_inventory', $dataInventory->jenis_inventory) == 'ASET' ? 'block' : 'none' }};">
                        <label for="no_seri" class="block text-sm font-medium text-gray-700 mb-2">No Seri</label>
                        <input type="text" id="no_seri" name="no_seri" value="{{ old('no_seri', $dataInventory->no_seri) }}" placeholder="Masukkan no seri" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div id="no_batch_field" style="display: {{ in_array(old('jenis_inventory', $dataInventory->jenis_inventory), ['PERSEDIAAN', 'FARMASI']) ? 'block' : 'none' }};">
                        <label for="no_batch" class="block text-sm font-medium text-gray-700 mb-2">
                            No Batch
                            <span id="no_batch_required_star" class="text-red-500" style="display: {{ (old('jenis_inventory', $dataInventory->jenis_inventory) == 'FARMASI') ? 'inline' : 'none' }};">*</span>
                            <span class="text-xs text-gray-500 block mt-0.5">Wajib diisi jika jenis Farmasi</span>
                        </label>
                        <input type="text" id="no_batch" name="no_batch" value="{{ old('no_batch', $dataInventory->no_batch) }}" placeholder="Masukkan no batch" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('no_batch') border-red-500 @enderror" {{ (old('jenis_inventory', $dataInventory->jenis_inventory) == 'FARMASI') ? 'required' : '' }}>
                        @error('no_batch')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="tanggal_kedaluwarsa_field" style="display: {{ in_array(old('jenis_inventory', $dataInventory->jenis_inventory), ['PERSEDIAAN', 'FARMASI']) ? 'block' : 'none' }};">
                        <label for="tanggal_kedaluwarsa" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Kedaluwarsa
                            <span id="tanggal_kedaluwarsa_required_star" class="text-red-500" style="display: {{ (old('jenis_inventory', $dataInventory->jenis_inventory) == 'FARMASI') ? 'inline' : 'none' }};">*</span>
                            <span class="text-xs text-gray-500 block mt-0.5">Wajib diisi jika jenis Farmasi</span>
                        </label>
                        <input type="date" id="tanggal_kedaluwarsa" name="tanggal_kedaluwarsa" value="{{ old('tanggal_kedaluwarsa', $dataInventory->tanggal_kedaluwarsa ? $dataInventory->tanggal_kedaluwarsa->format('Y-m-d') : '') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_kedaluwarsa') border-red-500 @enderror" {{ (old('jenis_inventory', $dataInventory->jenis_inventory) == 'FARMASI') ? 'required' : '' }}>
                        @error('tanggal_kedaluwarsa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="spesifikasi" class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi</label>
                        <textarea id="spesifikasi" name="spesifikasi" rows="3" placeholder="Masukkan spesifikasi" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('spesifikasi', $dataInventory->spesifikasi) }}</textarea>
                    </div>

                    <div>
                        <label for="upload_foto" class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                        @if($dataInventory->upload_foto)
                            <div class="mb-2">
                                <p class="text-sm text-gray-600 mb-2">Foto saat ini:</p>
                                <img src="{{ asset('storage/' . $dataInventory->upload_foto) }}" alt="Foto Inventory" class="h-32 w-auto rounded-md border border-gray-300">
                            </div>
                        @endif
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="upload_foto" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload file baru</span>
                                        <input id="upload_foto" name="upload_foto" type="file" accept="image/*" class="sr-only" onchange="previewImage(this)">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 10MB</p>
                            </div>
                        </div>
                        <div id="image-preview" class="mt-2 hidden">
                            <p class="text-sm text-gray-600 mb-2">Preview foto baru:</p>
                            <img id="preview-img" src="" alt="Preview" class="h-32 w-auto rounded-md border border-gray-300">
                        </div>
                        @error('upload_foto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="upload_dokumen" class="block text-sm font-medium text-gray-700 mb-2">Upload Dokumen</label>
                        @if($dataInventory->upload_dokumen)
                            <div class="mb-2">
                                <p class="text-sm text-gray-600 mb-2">Dokumen saat ini:</p>
                                <a href="{{ asset('storage/' . $dataInventory->upload_dokumen) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ basename($dataInventory->upload_dokumen) }}
                                </a>
                            </div>
                        @endif
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="upload_dokumen" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload file baru</span>
                                        <input id="upload_dokumen" name="upload_dokumen" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="sr-only">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG hingga 10MB</p>
                            </div>
                        </div>
                        @error('upload_dokumen')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('inventory.data-inventory.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Simpan</button>
        </div>
    </form>
</div>

<script>
    const JENIS_BARANG_OPTIONS = {
        'ASET': [ { value: 'ALKES', label: 'ALKES' }, { value: 'NON ALKES', label: 'NON ALKES' } ],
        'FARMASI': [ { value: 'OBAT', label: 'OBAT' }, { value: 'Vaksin', label: 'Vaksin' }, { value: 'BHP', label: 'BHP' }, { value: 'BMHP', label: 'BMHP' }, { value: 'REAGEN', label: 'REAGEN' }, { value: 'ALKES', label: 'ALKES' } ],
        'PERSEDIAAN': [ { value: 'ATK', label: 'ATK' }, { value: 'ART', label: 'ART' }, { value: 'CETAKAN UMUM', label: 'CETAKAN UMUM' }, { value: 'CETAK KHUSUS', label: 'CETAK KHUSUS' } ]
    };

    document.addEventListener('DOMContentLoaded', function() {
        const jenisInventorySelect = document.getElementById('jenis_inventory');
        const jenisBarangField = document.getElementById('jenis_barang_field');
        const jenisBarangSelect = document.getElementById('jenis_barang');
        const tipeField = document.getElementById('tipe_field');
        const noSeriField = document.getElementById('no_seri_field');
        const noBatchField = document.getElementById('no_batch_field');
        const tanggalKedaluwarsaField = document.getElementById('tanggal_kedaluwarsa_field');

        function updateJenisBarangOptions() {
            const jenisInventory = jenisInventorySelect.value;
            const options = JENIS_BARANG_OPTIONS[jenisInventory] || [];
            const currentValue = jenisBarangSelect.getAttribute('data-current-value') || jenisBarangSelect.value || '';
            jenisBarangSelect.innerHTML = '<option value="">Pilih Jenis Barang</option>';
            options.forEach(function(opt) {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                if (opt.value === currentValue) option.selected = true;
                jenisBarangSelect.appendChild(option);
            });
            if (options.length > 0) {
                jenisBarangField.style.display = 'block';
                jenisBarangSelect.setAttribute('required', 'required');
            } else {
                jenisBarangField.style.display = 'none';
                jenisBarangSelect.removeAttribute('required');
                jenisBarangSelect.value = '';
            }
        }

        function toggleFields() {
            const jenisInventory = jenisInventorySelect.value;
            updateJenisBarangOptions();

            if (jenisInventory === 'ASET') {
                // ASET: tampilkan tipe dan no_seri, sembunyikan no_batch dan tanggal_kedaluwarsa
                tipeField.style.display = 'block';
                noSeriField.style.display = 'block';
                noBatchField.style.display = 'none';
                tanggalKedaluwarsaField.style.display = 'none';
                
                document.getElementById('no_batch').value = '';
                document.getElementById('tanggal_kedaluwarsa').value = '';
                document.getElementById('no_batch').removeAttribute('required');
                document.getElementById('tanggal_kedaluwarsa').removeAttribute('required');
                const noBatchStar = document.getElementById('no_batch_required_star');
                const tanggalKedaluwarsaStar = document.getElementById('tanggal_kedaluwarsa_required_star');
                if (noBatchStar) noBatchStar.style.display = 'none';
                if (tanggalKedaluwarsaStar) tanggalKedaluwarsaStar.style.display = 'none';
            } else if (jenisInventory === 'PERSEDIAAN' || jenisInventory === 'FARMASI') {
                // PERSEDIAAN/FARMASI: sembunyikan tipe dan no_seri, tampilkan no_batch dan tanggal_kedaluwarsa
                tipeField.style.display = 'none';
                noSeriField.style.display = 'none';
                noBatchField.style.display = 'block';
                tanggalKedaluwarsaField.style.display = 'block';
                
                // FARMASI: No Batch dan Tanggal Kedaluwarsa wajib
                const noBatchInput = document.getElementById('no_batch');
                const tanggalKedaluwarsaInput = document.getElementById('tanggal_kedaluwarsa');
                const noBatchStar = document.getElementById('no_batch_required_star');
                const tanggalKedaluwarsaStar = document.getElementById('tanggal_kedaluwarsa_required_star');
                if (jenisInventory === 'FARMASI') {
                    if (noBatchInput) noBatchInput.setAttribute('required', 'required');
                    if (tanggalKedaluwarsaInput) tanggalKedaluwarsaInput.setAttribute('required', 'required');
                    if (noBatchStar) noBatchStar.style.display = 'inline';
                    if (tanggalKedaluwarsaStar) tanggalKedaluwarsaStar.style.display = 'inline';
                } else {
                    if (noBatchInput) noBatchInput.removeAttribute('required');
                    if (tanggalKedaluwarsaInput) tanggalKedaluwarsaInput.removeAttribute('required');
                    if (noBatchStar) noBatchStar.style.display = 'none';
                    if (tanggalKedaluwarsaStar) tanggalKedaluwarsaStar.style.display = 'none';
                }
                
                document.getElementById('tipe').value = '';
                document.getElementById('no_seri').value = '';
            } else {
                // Default: sembunyikan semua field khusus
                tipeField.style.display = 'none';
                noSeriField.style.display = 'none';
                noBatchField.style.display = 'none';
                tanggalKedaluwarsaField.style.display = 'none';
            }
        }

        // Event listener untuk perubahan jenis inventory
        jenisInventorySelect.addEventListener('change', toggleFields);
        
        // Jalankan saat halaman dimuat untuk set initial state
        toggleFields();
    });

    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('hidden');
        }
    }
</script>
@endsection

