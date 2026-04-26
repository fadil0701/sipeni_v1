@extends('layouts.app')

@section('content')
<div class="mb-4"><a href="{{ route('maintenance.kalibrasi-aset.index') }}" class="text-blue-600 hover:text-blue-900">Kembali ke daftar</a></div>
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h2 class="text-xl font-semibold mb-4">Edit Kalibrasi Aset</h2>
    <form method="POST" action="{{ route('maintenance.kalibrasi-aset.update', $kalibrasi->id_kalibrasi) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Register Aset</label>
                <select name="id_register_aset" required class="block w-full border border-gray-300 rounded-md px-3 py-2">
                    @foreach($registerAsets as $aset)
                        <option value="{{ $aset->id_register_aset }}" @selected(old('id_register_aset', $kalibrasi->id_register_aset) == $aset->id_register_aset)>{{ $aset->nomor_register }} - {{ $aset->inventory->dataBarang->nama_barang ?? '-' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Permintaan Kalibrasi (Opsional)</label>
                <select name="id_permintaan_pemeliharaan" class="block w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Tanpa permintaan</option>
                    @foreach($permintaans as $permintaan)
                        <option value="{{ $permintaan->id_permintaan_pemeliharaan }}" @selected(old('id_permintaan_pemeliharaan', $kalibrasi->id_permintaan_pemeliharaan) == $permintaan->id_permintaan_pemeliharaan)>{{ $permintaan->no_permintaan_pemeliharaan }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div><label class="block text-sm font-medium mb-2">Tanggal Kalibrasi</label><input type="date" name="tanggal_kalibrasi" required value="{{ old('tanggal_kalibrasi', optional($kalibrasi->tanggal_kalibrasi)->format('Y-m-d')) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Tanggal Berlaku</label><input type="date" name="tanggal_berlaku" required value="{{ old('tanggal_berlaku', optional($kalibrasi->tanggal_berlaku)->format('Y-m-d')) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Tanggal Kadaluarsa</label><input type="date" name="tanggal_kadaluarsa" required value="{{ old('tanggal_kadaluarsa', optional($kalibrasi->tanggal_kadaluarsa)->format('Y-m-d')) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Status</label><select name="status_kalibrasi" required class="block w-full border border-gray-300 rounded-md px-3 py-2">@foreach(['VALID','KADALUARSA','MENUNGGU','DITOLAK'] as $status)<option value="{{ $status }}" @selected(old('status_kalibrasi', $kalibrasi->status_kalibrasi)===$status)>{{ $status }}</option>@endforeach</select></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium mb-2">Lembaga</label><input type="text" name="lembaga_kalibrasi" value="{{ old('lembaga_kalibrasi', $kalibrasi->lembaga_kalibrasi) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">No Sertifikat</label><input type="text" name="no_sertifikat" value="{{ old('no_sertifikat', $kalibrasi->no_sertifikat) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Biaya</label><input type="number" step="0.01" min="0" name="biaya_kalibrasi" value="{{ old('biaya_kalibrasi', $kalibrasi->biaya_kalibrasi) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">File Sertifikat (Upload)</label>
                <input id="file_sertifikat_upload_edit" type="file" name="file_sertifikat" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                <label for="file_sertifikat_upload_edit" class="inline-flex items-center px-3 py-2 border border-blue-200 bg-blue-50 text-blue-700 text-sm font-medium rounded-md cursor-pointer hover:bg-blue-100">
                    Pilih File
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-600 text-white">UPLOAD</span>
                </label>
                <p id="file_sertifikat_upload_edit_name" class="mt-1 text-xs text-gray-500">Belum ada file dipilih</p>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/DOC/DOCX/JPG/PNG (maks 4MB)</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">File Sertifikat (Open Kamera)</label>
                <input id="file_sertifikat_kamera_edit" type="file" name="file_sertifikat_kamera" accept="image/*" capture="environment" class="hidden">
                <label for="file_sertifikat_kamera_edit" class="inline-flex items-center px-3 py-2 border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-medium rounded-md cursor-pointer hover:bg-emerald-100">
                    Buka Kamera
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-600 text-white">KAMERA</span>
                </label>
                <p id="file_sertifikat_kamera_edit_name" class="mt-1 text-xs text-gray-500">Belum ada foto diambil</p>
                <p class="mt-1 text-xs text-gray-500">Gunakan kamera perangkat (maks 4MB)</p>
            </div>
        </div>
        @if(!empty($kalibrasi->file_sertifikat))
            @php
                $existingFileUrl = asset('storage/' . $kalibrasi->file_sertifikat);
                $existingExt = strtolower(pathinfo($kalibrasi->file_sertifikat, PATHINFO_EXTENSION));
                $existingIsImage = in_array($existingExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                $existingIsDocLike = in_array($existingExt, ['pdf', 'doc', 'docx'], true);
            @endphp
            <div id="file_sertifikat_preview_edit_existing" class="rounded-md border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-medium text-gray-600 mb-2">File Sertifikat Saat Ini</p>
                <a href="{{ $existingFileUrl }}" target="_blank" rel="noopener" class="inline-block text-sm text-blue-600 hover:text-blue-800 mb-2">Buka File</a>
                @if($existingIsImage)
                    <img src="{{ $existingFileUrl }}" alt="File sertifikat saat ini" class="max-h-64 rounded border border-gray-200">
                @elseif($existingIsDocLike)
                    <p class="text-xs text-gray-500">File non-gambar ditampilkan sebagai link.</p>
                @endif
            </div>
        @endif
        <div id="file_sertifikat_preview_edit_new" class="hidden rounded-md border border-gray-200 bg-gray-50 p-3">
            <p class="text-xs font-medium text-gray-600 mb-2">Preview File Baru</p>
            <div id="file_sertifikat_preview_edit_new_content"></div>
        </div>
        <div><label class="block text-sm font-medium mb-2">Keterangan</label><textarea name="keterangan" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('keterangan', $kalibrasi->keterangan) }}</textarea></div>
        <div class="flex justify-end space-x-3"><a href="{{ route('maintenance.kalibrasi-aset.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Batal</a><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button></div>
    </form>
</div>
@push('scripts')
<script>
    (function () {
        const uploadInput = document.getElementById('file_sertifikat_upload_edit');
        const kameraInput = document.getElementById('file_sertifikat_kamera_edit');
        const uploadName = document.getElementById('file_sertifikat_upload_edit_name');
        const kameraName = document.getElementById('file_sertifikat_kamera_edit_name');
        const previewWrap = document.getElementById('file_sertifikat_preview_edit_new');
        const previewContent = document.getElementById('file_sertifikat_preview_edit_new_content');

        function renderPreview(file) {
            if (!previewWrap || !previewContent || !file) return;
            const url = URL.createObjectURL(file);
            const isImage = file.type.startsWith('image/');
            const isDocLike = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type);
            if (isImage) {
                previewContent.innerHTML = '<img src="' + url + '" alt="Preview file sertifikat baru" class="max-h-64 rounded border border-gray-200">';
            } else if (isDocLike) {
                previewContent.innerHTML = '<a href="' + url + '" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:text-blue-800 underline">' + file.name + '</a><p class="mt-1 text-xs text-gray-500">File non-gambar ditampilkan sebagai link.</p>';
            } else {
                previewContent.innerHTML = '<p class="text-sm text-gray-700">' + file.name + '</p>';
            }
            previewWrap.classList.remove('hidden');
        }
        if (uploadInput && uploadName) {
            uploadInput.addEventListener('change', function () {
                uploadName.textContent = this.files && this.files[0] ? this.files[0].name : 'Belum ada file dipilih';
                if (this.files && this.files[0]) renderPreview(this.files[0]);
            });
        }
        if (kameraInput && kameraName) {
            kameraInput.addEventListener('change', function () {
                kameraName.textContent = this.files && this.files[0] ? this.files[0].name : 'Belum ada foto diambil';
                if (this.files && this.files[0]) renderPreview(this.files[0]);
            });
        }
    })();
</script>
@endpush
@endsection
