@extends('layouts.app')

@section('content')
<div class="mb-4"><a href="{{ route('maintenance.service-report.index') }}" class="text-blue-600 hover:text-blue-900">Kembali ke daftar</a></div>
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h2 class="text-xl font-semibold mb-4">Edit Service Report</h2>
    <form method="POST" action="{{ route('maintenance.service-report.update', $serviceReport->id_service_report) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium mb-2">Tanggal Service</label><input type="date" name="tanggal_service" value="{{ old('tanggal_service', optional($serviceReport->tanggal_service)->format('Y-m-d')) }}" required class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($serviceReport->tanggal_selesai)->format('Y-m-d')) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Status</label><select name="status_service" required class="block w-full border border-gray-300 rounded-md px-3 py-2">@foreach(['MENUNGGU','DIPROSES','SELESAI','DITOLAK','DIBATALKAN'] as $status)<option value="{{ $status }}" @selected(old('status_service', $serviceReport->status_service)===$status)>{{ $status }}</option>@endforeach</select></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-2">Jenis Service</label><select name="jenis_service" required class="block w-full border border-gray-300 rounded-md px-3 py-2">@foreach(['RUTIN','KALIBRASI','PERBAIKAN','PENGGANTIAN_SPAREPART'] as $jenis)<option value="{{ $jenis }}" @selected(old('jenis_service', $serviceReport->jenis_service)===$jenis)>{{ $jenis }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-2">Kondisi Setelah Service</label><select name="kondisi_setelah_service" class="block w-full border border-gray-300 rounded-md px-3 py-2"><option value="">-</option>@foreach(['BAIK','RUSAK_RINGAN','RUSAK_BERAT','TIDAK_BISA_DIPERBAIKI'] as $kondisi)<option value="{{ $kondisi }}" @selected(old('kondisi_setelah_service', $serviceReport->kondisi_setelah_service)===$kondisi)>{{ $kondisi }}</option>@endforeach</select></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-2">Vendor</label><input type="text" name="vendor" value="{{ old('vendor', $serviceReport->vendor) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div>
                <label class="block text-sm font-medium mb-2">Teknisi</label>
                @php($selectedTeknisi = old('teknisi', $serviceReport->teknisi))
                <select name="teknisi" class="block w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Pilih Teknisi</option>
                    @if(!empty($selectedTeknisi) && !$teknisiPegawais->contains(fn($item) => $item->nama_pegawai === $selectedTeknisi))
                        <option value="{{ $selectedTeknisi }}" selected>{{ $selectedTeknisi }} (data existing)</option>
                    @endif
                    @foreach($teknisiPegawais as $teknisi)
                        <option value="{{ $teknisi->nama_pegawai }}" @selected($selectedTeknisi === $teknisi->nama_pegawai)>
                            {{ $teknisi->nama_pegawai }}
                        </option>
                    @endforeach
                </select>
                @if($teknisiPegawais->isEmpty())
                    <p class="mt-1 text-xs text-amber-600">Belum ada pegawai dengan jabatan teknisi di master pegawai.</p>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-2">Biaya Service</label><input type="number" step="0.01" min="0" name="biaya_service" value="{{ old('biaya_service', $serviceReport->biaya_service) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Biaya Sparepart</label><input type="number" step="0.01" min="0" name="biaya_sparepart" value="{{ old('biaya_sparepart', $serviceReport->biaya_sparepart) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
        </div>
        <div><label class="block text-sm font-medium mb-2">Deskripsi Kerja</label><textarea name="deskripsi_kerja" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('deskripsi_kerja', $serviceReport->deskripsi_kerja) }}</textarea></div>
        <div><label class="block text-sm font-medium mb-2">Tindakan</label><textarea name="tindakan_yang_dilakukan" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('tindakan_yang_dilakukan', $serviceReport->tindakan_yang_dilakukan) }}</textarea></div>
        <div><label class="block text-sm font-medium mb-2">Sparepart Diganti</label><textarea name="sparepart_yang_diganti" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('sparepart_yang_diganti', $serviceReport->sparepart_yang_diganti) }}</textarea></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">File Laporan (Upload)</label>
                <input id="file_laporan_upload_edit" type="file" name="file_laporan" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                <label for="file_laporan_upload_edit" class="inline-flex items-center px-3 py-2 border border-blue-200 bg-blue-50 text-blue-700 text-sm font-medium rounded-md cursor-pointer hover:bg-blue-100">
                    Pilih File
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-600 text-white">UPLOAD</span>
                </label>
                <p id="file_laporan_upload_edit_name" class="mt-1 text-xs text-gray-500">Belum ada file dipilih</p>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/DOC/DOCX/JPG/PNG (maks 4MB)</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">File Laporan (Open Kamera)</label>
                <input id="file_laporan_kamera_edit" type="file" name="file_laporan_kamera" accept="image/*" capture="environment" class="hidden">
                <label for="file_laporan_kamera_edit" class="inline-flex items-center px-3 py-2 border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-medium rounded-md cursor-pointer hover:bg-emerald-100">
                    Buka Kamera
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-600 text-white">KAMERA</span>
                </label>
                <p id="file_laporan_kamera_edit_name" class="mt-1 text-xs text-gray-500">Belum ada foto diambil</p>
                <p class="mt-1 text-xs text-gray-500">Gunakan kamera perangkat (maks 4MB)</p>
            </div>
        </div>
        @if(!empty($serviceReport->file_laporan))
            @php
                $existingFileUrl = asset('storage/' . $serviceReport->file_laporan);
                $existingExt = strtolower(pathinfo($serviceReport->file_laporan, PATHINFO_EXTENSION));
                $existingIsImage = in_array($existingExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                $existingIsDocLike = in_array($existingExt, ['pdf', 'doc', 'docx'], true);
            @endphp
            <div id="file_laporan_preview_edit_existing" class="rounded-md border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-medium text-gray-600 mb-2">File Laporan Saat Ini</p>
                <a href="{{ $existingFileUrl }}" target="_blank" rel="noopener" class="inline-block text-sm text-blue-600 hover:text-blue-800 mb-2">Buka File</a>
                @if($existingIsImage)
                    <img src="{{ $existingFileUrl }}" alt="File laporan saat ini" class="max-h-64 rounded border border-gray-200">
                @elseif($existingIsDocLike)
                    <p class="text-xs text-gray-500">File non-gambar ditampilkan sebagai link.</p>
                @endif
            </div>
        @endif
        <div id="file_laporan_preview_edit_new" class="hidden rounded-md border border-gray-200 bg-gray-50 p-3">
            <p class="text-xs font-medium text-gray-600 mb-2">Preview File Baru</p>
            <div id="file_laporan_preview_edit_new_content"></div>
        </div>
        <div><label class="block text-sm font-medium mb-2">Keterangan</label><textarea name="keterangan" rows="2" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('keterangan', $serviceReport->keterangan) }}</textarea></div>
        <div class="flex justify-end space-x-3"><a href="{{ route('maintenance.service-report.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Batal</a><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button></div>
    </form>
</div>
@push('scripts')
<script>
    (function () {
        const uploadInput = document.getElementById('file_laporan_upload_edit');
        const kameraInput = document.getElementById('file_laporan_kamera_edit');
        const uploadName = document.getElementById('file_laporan_upload_edit_name');
        const kameraName = document.getElementById('file_laporan_kamera_edit_name');
        const previewWrap = document.getElementById('file_laporan_preview_edit_new');
        const previewContent = document.getElementById('file_laporan_preview_edit_new_content');

        function renderPreview(file) {
            if (!previewWrap || !previewContent || !file) return;
            const url = URL.createObjectURL(file);
            const isImage = file.type.startsWith('image/');
            const isDocLike = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type);
            if (isImage) {
                previewContent.innerHTML = '<img src="' + url + '" alt="Preview file laporan baru" class="max-h-64 rounded border border-gray-200">';
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
