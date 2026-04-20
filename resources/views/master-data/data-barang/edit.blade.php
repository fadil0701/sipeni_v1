@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-data.data-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Data Barang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Data Barang</h2>
    </div>
    
    <form action="{{ route('master-data.data-barang.update', $dataBarang->id_data_barang) }}" method="POST" class="p-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Dasar -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_subjenis_barang" class="block text-sm font-medium text-gray-700 mb-2">
                            Subjenis Barang <span class="text-red-500">*</span>
                        </label>
                        <select id="id_subjenis_barang" name="id_subjenis_barang" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_subjenis_barang') border-red-500 @enderror">
                            <option value="">Pilih Subjenis Barang</option>
                            @foreach($subjenisBarangs as $subjenisBarang)
                                <option value="{{ $subjenisBarang->id_subjenis_barang }}" {{ old('id_subjenis_barang', $dataBarang->id_subjenis_barang) == $subjenisBarang->id_subjenis_barang ? 'selected' : '' }}>
                                    {{ $subjenisBarang->jenisBarang->kategoriBarang->kodeBarang->kode_barang ?? '' }} - {{ $subjenisBarang->nama_subjenis_barang }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_subjenis_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="id_satuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Satuan <span class="text-red-500">*</span>
                        </label>
                        <select id="id_satuan" name="id_satuan" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_satuan') border-red-500 @enderror">
                            <option value="">Pilih Satuan</option>
                            @foreach($satuans as $satuan)
                                <option value="{{ $satuan->id_satuan }}" {{ old('id_satuan', $dataBarang->id_satuan) == $satuan->id_satuan ? 'selected' : '' }}>{{ $satuan->nama_satuan }}</option>
                            @endforeach
                        </select>
                        @error('id_satuan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="kode_data_barang" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Data Barang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="kode_data_barang" name="kode_data_barang" required value="{{ old('kode_data_barang', $dataBarang->kode_data_barang) }}" placeholder="Masukkan kode data barang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kode_data_barang') border-red-500 @enderror">
                        @error('kode_data_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="nama_barang" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Barang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_barang" name="nama_barang" required value="{{ old('nama_barang', $dataBarang->nama_barang) }}" placeholder="Masukkan nama barang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_barang') border-red-500 @enderror">
                        @error('nama_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Tambahan</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Masukkan deskripsi barang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('deskripsi', $dataBarang->deskripsi) }}</textarea>
                    </div>

                    <div>
                        <label for="upload_foto" class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                        @if($dataBarang->upload_foto || $dataBarang->foto_barang)
                            <div class="mb-2">
                                <p class="text-sm text-gray-600 mb-2">Foto saat ini:</p>
                                <img src="{{ $dataBarang->upload_foto ? asset('storage/' . $dataBarang->upload_foto) : $dataBarang->foto_barang }}" alt="Foto Barang" class="h-32 w-auto rounded-md border border-gray-300">
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
                        <label for="foto_barang" class="block text-sm font-medium text-gray-700 mb-2">URL Foto Barang (Opsional)</label>
                        <input type="text" id="foto_barang" name="foto_barang" value="{{ old('foto_barang', $dataBarang->foto_barang) }}" placeholder="Masukkan URL foto barang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">Atau masukkan URL foto jika sudah diupload di tempat lain</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('master-data.data-barang.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Simpan</button>
        </div>
    </form>
</div>

<script>
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

