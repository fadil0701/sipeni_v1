@extends('layouts.app')

@section('content')
@php
    use App\Enums\PemeliharaanJenisPelaksana;
    $invEdit = $serviceReport->registerAset->inventory ?? null;
    $noSeriEdit = trim((string) (
        $serviceReport->registerAset->inventoryItem->no_seri
        ?? ($invEdit->no_seri ?? '')
    ));
    $jp = PemeliharaanJenisPelaksana::tryFrom((string) ($serviceReport->permintaanPemeliharaan->jenis_pelaksana ?? ''));
    $defaultMode = old('pelaksana_mode');
    if (! $defaultMode) {
        if (filled($serviceReport->vendor)) {
            $defaultMode = 'EKSTERNAL';
        } elseif ($jp && $jp->requiresVendorName()) {
            $defaultMode = 'EKSTERNAL';
        } else {
            $defaultMode = 'INTERNAL';
        }
    }
    $selectedTeknisi = old('teknisi', $serviceReport->teknisi);
    $defaultCatatan = old('catatan', $serviceReport->keterangan ?: $serviceReport->rekomendasi_catatan);
@endphp
<div class="mb-4"><a href="{{ route('maintenance.service-report.index') }}" class="text-blue-600 hover:text-blue-900">Kembali ke daftar</a></div>
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h2 class="text-xl font-semibold mb-4">Edit Laporan Servis</h2>
    <form method="POST" action="{{ route('maintenance.service-report.update', $serviceReport->id_service_report) }}" enctype="multipart/form-data" class="space-y-4" id="form-service-report">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-md border border-gray-200 bg-gray-50 p-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                <p class="text-sm font-medium text-gray-900">{{ $invEdit->dataBarang->nama_barang ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Register</label>
                <p class="text-sm font-medium text-gray-900">{{ $serviceReport->registerAset->nomor_register ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Merk</label>
                <p class="text-sm font-medium text-gray-900">{{ trim((string) ($invEdit->merk ?? '')) !== '' ? $invEdit->merk : '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                <p class="text-sm font-medium text-gray-900">{{ trim((string) ($invEdit->tipe ?? '')) !== '' ? $invEdit->tipe : '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Seri</label>
                <p class="text-sm font-medium text-gray-900">{{ $noSeriEdit !== '' ? $noSeriEdit : '-' }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium mb-2">Tanggal Servis</label><input type="date" name="tanggal_service" value="{{ old('tanggal_service', optional($serviceReport->tanggal_service)->format('Y-m-d')) }}" required class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($serviceReport->tanggal_selesai)->format('Y-m-d')) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Status</label><select name="status_service" required class="block w-full border border-gray-300 rounded-md px-3 py-2">@foreach(['MENUNGGU','DIPROSES','SELESAI','DITOLAK','DIBATALKAN'] as $status)<option value="{{ $status }}" @selected(old('status_service', $serviceReport->status_service)===$status)>{{ $status }}</option>@endforeach</select></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-2">Jenis Servis</label><select name="jenis_service" required class="block w-full border border-gray-300 rounded-md px-3 py-2">@foreach(['RUTIN','KALIBRASI','PERBAIKAN','PENGGANTIAN_SPAREPART'] as $jenis)<option value="{{ $jenis }}" @selected(old('jenis_service', $serviceReport->jenis_service)===$jenis)>{{ $jenis }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-2">Kondisi Setelah Servis</label><select name="kondisi_setelah_service" class="block w-full border border-gray-300 rounded-md px-3 py-2"><option value="">-</option>@foreach(['BAIK','RUSAK_RINGAN','RUSAK_BERAT','TIDAK_BISA_DIPERBAIKI'] as $kondisi)<option value="{{ $kondisi }}" @selected(old('kondisi_setelah_service', $serviceReport->kondisi_setelah_service)===$kondisi)>{{ $kondisi }}</option>@endforeach</select></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-md border border-gray-200 p-4 bg-gray-50/50">
            <div>
                <label class="block text-sm font-medium mb-2">Jenis Pelaksana <span class="text-red-500">*</span></label>
                <select name="pelaksana_mode" id="pelaksana_mode" required class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white">
                    <option value="INTERNAL" @selected($defaultMode === 'INTERNAL')>Internal (ATEM / IT Support)</option>
                    <option value="EKSTERNAL" @selected($defaultMode === 'EKSTERNAL')>Vendor / Kontrak Service</option>
                </select>
            </div>
            <div id="field-vendor-wrap" class="{{ $defaultMode === 'EKSTERNAL' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium mb-2">Nama Vendor <span class="text-red-500">*</span></label>
                <input type="text" name="vendor" id="vendor_input" value="{{ old('vendor', $serviceReport->vendor) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white" placeholder="Nama vendor / kontraktor">
                @error('vendor')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-teknisi-internal" class="{{ $defaultMode === 'INTERNAL' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium mb-2">Teknisi Internal <span class="text-red-500">*</span></label>
                <select name="teknisi" id="teknisi_select" class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white">
                    <option value="">Pilih Teknisi</option>
                    @if(!empty($selectedTeknisi) && $defaultMode === 'INTERNAL' && !$teknisiPegawais->contains(fn($item) => $item->nama_pegawai === $selectedTeknisi))
                        <option value="{{ $selectedTeknisi }}" selected>{{ $selectedTeknisi }} (data existing)</option>
                    @endif
                    @foreach($teknisiPegawais as $teknisi)
                        <option value="{{ $teknisi->nama_pegawai }}" @selected($defaultMode === 'INTERNAL' && $selectedTeknisi === $teknisi->nama_pegawai)>
                            {{ $teknisi->nama_pegawai }}@if($teknisi->masterJabatan) — {{ $teknisi->masterJabatan->nama_jabatan }}@endif
                        </option>
                    @endforeach
                </select>
                @if($teknisiPegawais->isEmpty())
                    <p class="mt-1 text-xs text-amber-600">Belum ada pegawai dengan jabatan ATEM / IT Support.</p>
                @endif
                @error('teknisi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-teknisi-eksternal" class="{{ $defaultMode === 'EKSTERNAL' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium mb-2">Nama Teknisi Vendor</label>
                <input type="text" name="teknisi_eksternal" id="teknisi_eksternal_input" value="{{ $defaultMode === 'EKSTERNAL' ? $selectedTeknisi : '' }}" class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white" placeholder="Nama teknisi pihak luar (opsional)">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Rekomendasi <span class="text-red-500">*</span> (wajib jika SELESAI)</label>
                <select name="rekomendasi" id="rekomendasi_select" class="block w-full border border-gray-300 rounded-md px-3 py-2 @error('rekomendasi') border-red-500 @enderror">
                    <option value="">Pilih rekomendasi</option>
                    @foreach(\App\Enums\PemeliharaanRekomendasi::cases() as $rek)
                        <option value="{{ $rek->value }}" @selected(old('rekomendasi', $serviceReport->rekomendasi)===$rek->value)>{{ $rek->label() }}</option>
                    @endforeach
                </select>
                @error('rekomendasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium mb-2">Biaya Servis</label><input type="number" step="0.01" min="0" name="biaya_service" value="{{ old('biaya_service', $serviceReport->biaya_service) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
                <div><label class="block text-sm font-medium mb-2">Biaya Sparepart</label><input type="number" step="0.01" min="0" name="biaya_sparepart" value="{{ old('biaya_sparepart', $serviceReport->biaya_sparepart) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            </div>
        </div>

        @include('maintenance.service-report._sparepart-table', [
            'sparepartRows' => old('spareparts') ? collect(old('spareparts')) : ($serviceReport->spareparts->isNotEmpty() ? $serviceReport->spareparts : collect([['nama_sparepart' => '', 'merk' => '', 'nomor_seri' => '']])),
        ])

        <div><label class="block text-sm font-medium mb-2">Deskripsi Kerja</label><textarea name="deskripsi_kerja" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('deskripsi_kerja', $serviceReport->deskripsi_kerja) }}</textarea></div>
        <div><label class="block text-sm font-medium mb-2">Tindakan</label><textarea name="tindakan_yang_dilakukan" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('tindakan_yang_dilakukan', $serviceReport->tindakan_yang_dilakukan) }}</textarea></div>
        <div>
            <label class="block text-sm font-medium mb-2">Catatan</label>
            <textarea name="catatan" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Catatan servis, rekomendasi, atau keterangan lainnya...">{{ $defaultCatatan }}</textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">File Laporan (Upload)</label>
                <input id="file_laporan_upload_edit" type="file" name="file_laporan" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                <label for="file_laporan_upload_edit" class="inline-flex items-center px-3 py-2 border border-blue-200 bg-blue-50 text-blue-700 text-sm font-medium rounded-md cursor-pointer hover:bg-blue-100">Pilih File</label>
                <p id="file_laporan_upload_edit_name" class="mt-1 text-xs text-gray-500">Belum ada file dipilih</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">File Laporan (Open Kamera)</label>
                <input id="file_laporan_kamera_edit" type="file" name="file_laporan_kamera" accept="image/*" capture="environment" class="hidden">
                <label for="file_laporan_kamera_edit" class="inline-flex items-center px-3 py-2 border border-green-200 bg-green-100 text-green-900 text-sm font-medium rounded-md cursor-pointer hover:bg-green-100">Buka Kamera</label>
                <p id="file_laporan_kamera_edit_name" class="mt-1 text-xs text-gray-500">Belum ada foto diambil</p>
            </div>
        </div>
        @if(!empty($serviceReport->file_laporan))
            <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-medium text-gray-600 mb-2">File Laporan Saat Ini</p>
                <a href="{{ route('media.show', ['path' => $serviceReport->file_laporan]) }}" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:text-blue-800">Buka File</a>
            </div>
        @endif
        <div class="flex justify-end space-x-3"><a href="{{ route('maintenance.service-report.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Batal</a><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button></div>
    </form>
</div>
@push('scripts')
<script>
(function () {
    const PENDING = @json(\App\Enums\PemeliharaanRekomendasi::PendingSparepart->value);
    const pelaksanaMode = document.getElementById('pelaksana_mode');
    const rekomendasiSelect = document.getElementById('rekomendasi_select');
    const teknisiSelect = document.getElementById('teknisi_select');
    const teknisiEksternal = document.getElementById('teknisi_eksternal_input');
    const form = document.getElementById('form-service-report');

    function syncPelaksanaUI() {
        const eksternal = pelaksanaMode?.value === 'EKSTERNAL';
        document.getElementById('field-vendor-wrap')?.classList.toggle('hidden', !eksternal);
        document.getElementById('field-teknisi-internal')?.classList.toggle('hidden', eksternal);
        document.getElementById('field-teknisi-eksternal')?.classList.toggle('hidden', !eksternal);
        if (teknisiSelect) teknisiSelect.disabled = eksternal;
        if (teknisiEksternal) teknisiEksternal.disabled = !eksternal;
    }
    function syncSparepartUI() {
        const show = rekomendasiSelect?.value === PENDING;
        const section = document.getElementById('sparepart-section');
        section?.classList.toggle('hidden', !show);
        // Jangan disable input file: browser tidak mengirim file dari input disabled.
    }
    function reindexSparepartRows() {
        document.querySelectorAll('#sparepart-tbody .sparepart-row').forEach((tr, idx) => {
            tr.querySelectorAll('input, select').forEach((input) => {
                if (!input.name) return;
                input.name = input.name.replace(/spareparts\[\d+\]/, 'spareparts[' + idx + ']');
            });
            const fotoInput = tr.querySelector('.sparepart-foto-input');
            const fotoLabel = tr.querySelector('label[for]');
            if (fotoInput) {
                const id = 'sparepart_foto_' + idx + '_' + Date.now();
                fotoInput.id = id;
                if (fotoLabel) fotoLabel.setAttribute('for', id);
            }
        });
    }
    function buildSparepartRowHtml() {
        const id = 'sparepart_foto_new_' + Date.now();
        return `
            <td class="px-3 py-2 align-top"><input type="text" name="spareparts[0][nama_sparepart]" class="block w-full border border-gray-300 rounded-md px-2 py-1.5" placeholder="Nama spare part"></td>
            <td class="px-3 py-2 align-top"><input type="text" name="spareparts[0][merk]" class="block w-full border border-gray-300 rounded-md px-2 py-1.5" placeholder="Merk"></td>
            <td class="px-3 py-2 align-top"><input type="text" name="spareparts[0][nomor_seri]" class="block w-full border border-gray-300 rounded-md px-2 py-1.5" placeholder="No. seri"></td>
            <td class="px-3 py-2 align-top">
                <div class="flex flex-col items-start gap-2">
                    <input type="file" id="${id}" name="spareparts[0][foto]" accept="image/*" class="sr-only sparepart-foto-input">
                    <label for="${id}" class="inline-flex items-center px-3 py-1.5 border border-blue-200 bg-blue-50 text-blue-700 text-xs font-medium rounded-md cursor-pointer hover:bg-blue-100">Pilih Foto</label>
                    <button type="button" class="sparepart-foto-thumb hidden block w-14 h-14 rounded border border-gray-200 overflow-hidden bg-gray-50 p-0 focus:outline-none focus:ring-2 focus:ring-blue-400" title="Klik untuk memperbesar" data-full-src="">
                        <img src="" alt="Thumbnail spare part" class="sparepart-foto-thumb-img w-full h-full object-cover pointer-events-none">
                    </button>
                    <p class="sparepart-foto-name text-xs text-gray-500">Belum ada foto</p>
                </div>
            </td>
            <td class="px-3 py-2 align-top">
                <button type="button" class="btn-remove-sparepart inline-flex items-center justify-center w-8 h-8 rounded-full text-red-600 hover:bg-red-50" title="Hapus">
                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/></svg>
                </button>
            </td>
        `;
    }
    document.getElementById('btn-add-sparepart')?.addEventListener('click', function () {
        const tbody = document.getElementById('sparepart-tbody');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.className = 'sparepart-row';
        tr.innerHTML = buildSparepartRowHtml();
        tbody.appendChild(tr);
        reindexSparepartRows();
    });
    document.getElementById('sparepart-tbody')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-sparepart');
        if (!btn) return;
        const rows = document.querySelectorAll('#sparepart-tbody .sparepart-row');
        if (rows.length <= 1) return;
        btn.closest('tr')?.remove();
        reindexSparepartRows();
    });
    document.getElementById('sparepart-tbody')?.addEventListener('change', function (e) {
        if (!e.target.classList.contains('sparepart-foto-input')) return;
        const td = e.target.closest('td');
        const nameEl = td?.querySelector('.sparepart-foto-name');
        const thumbBtn = td?.querySelector('.sparepart-foto-thumb');
        const thumbImg = td?.querySelector('.sparepart-foto-thumb-img');
        const file = e.target.files?.[0];
        const isImage = window.SipeniSparepartFoto
            ? window.SipeniSparepartFoto.isImageFile(file)
            : (file && file.type.startsWith('image/'));

        if (nameEl) {
            nameEl.textContent = file
                ? (isImage ? 'Klik foto untuk memperbesar' : file.name)
                : 'Belum ada foto';
        }
        if (!thumbBtn || !thumbImg) return;

        if (thumbImg.dataset.objectUrl) {
            URL.revokeObjectURL(thumbImg.dataset.objectUrl);
            delete thumbImg.dataset.objectUrl;
        }

        if (file && isImage) {
            const url = URL.createObjectURL(file);
            thumbImg.dataset.objectUrl = url;
            thumbImg.src = url;
            thumbBtn.setAttribute('data-full-src', url);
            thumbBtn.classList.remove('hidden');
        } else {
            thumbImg.removeAttribute('src');
            thumbBtn.setAttribute('data-full-src', '');
            thumbBtn.classList.add('hidden');
        }
    });
    form?.addEventListener('submit', function () {
        document.querySelectorAll('#sparepart-section input, #sparepart-section select').forEach((el) => {
            el.disabled = false;
        });
        if (pelaksanaMode?.value === 'EKSTERNAL' && teknisiEksternal && teknisiSelect) {
            teknisiSelect.disabled = true;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'teknisi';
            hidden.value = teknisiEksternal.value || '';
            form.appendChild(hidden);
        }
    });
    pelaksanaMode?.addEventListener('change', syncPelaksanaUI);
    rekomendasiSelect?.addEventListener('change', syncSparepartUI);
    syncPelaksanaUI();
    syncSparepartUI();
})();
</script>
@endpush
@endsection
