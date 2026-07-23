@extends('layouts.app')

@section('content')
@php
    use App\Enums\PemeliharaanJenisPelaksana;
    $selectedJenisPelaksana = old('pelaksana_mode');
    if (! $selectedJenisPelaksana && $selectedPermintaan?->jenis_pelaksana) {
        $jp = PemeliharaanJenisPelaksana::tryFrom((string) $selectedPermintaan->jenis_pelaksana);
        $selectedJenisPelaksana = ($jp && $jp->requiresVendorName()) ? 'EKSTERNAL' : 'INTERNAL';
    }
    $selectedJenisPelaksana = $selectedJenisPelaksana ?: 'INTERNAL';
    $defaultVendor = old('vendor', $selectedPermintaan?->nama_vendor ?? '');
    $defaultTeknisi = old('teknisi', $selectedPermintaan?->pegawaiPelaksana?->nama_pegawai ?? '');
    $defaultCatatan = old('catatan', old('keterangan', old('rekomendasi_catatan')));
    $defaultDeskripsiKerja = old('deskripsi_kerja', $selectedPermintaan?->deskripsi_kerusakan ?? '');
    $deskripsiKerusakanByPermintaan = $permintaans->mapWithKeys(function ($p) {
        return [(string) $p->id_permintaan_pemeliharaan => (string) ($p->deskripsi_kerusakan ?? '')];
    })->all();
@endphp
<div class="mb-4"><a href="{{ route('maintenance.service-report.index') }}" class="text-blue-600 hover:text-blue-900">Kembali ke daftar</a></div>
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h2 class="text-xl font-semibold mb-4">Tambah Laporan Servis</h2>
    <form method="POST" action="{{ route('maintenance.service-report.store') }}" enctype="multipart/form-data" class="space-y-4" id="form-service-report">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-2">Permintaan Pemeliharaan</label>
            <select name="id_permintaan_pemeliharaan" id="id_permintaan_pemeliharaan" required class="block w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="" data-nama-barang="" data-nomor-register="" data-merk="" data-tipe="" data-no-seri="" data-pelaksana-mode="" data-vendor="" data-teknisi="">Pilih permintaan</option>
                @foreach($permintaans as $permintaan)
                    @php
                        $inv = $permintaan->registerAset->inventory ?? null;
                        $namaBarang = $inv->dataBarang->nama_barang ?? '-';
                        $nomorRegister = $permintaan->registerAset->nomor_register ?? '-';
                        $merk = trim((string) ($inv->merk ?? ''));
                        $tipe = trim((string) ($inv->tipe ?? ''));
                        $noSeri = trim((string) ($permintaan->registerAset->inventoryItem->no_seri ?? ($inv->no_seri ?? '')));
                        $jp = PemeliharaanJenisPelaksana::tryFrom((string) ($permintaan->jenis_pelaksana ?? ''));
                        $mode = ($jp && $jp->requiresVendorName()) ? 'EKSTERNAL' : 'INTERNAL';
                        $vendorName = $permintaan->nama_vendor ?? '';
                        $teknisiName = $permintaan->pegawaiPelaksana->nama_pegawai ?? '';
                    @endphp
                    <option
                        value="{{ $permintaan->id_permintaan_pemeliharaan }}"
                        data-nama-barang="{{ $namaBarang }}"
                        data-nomor-register="{{ $nomorRegister }}"
                        data-merk="{{ $merk }}"
                        data-tipe="{{ $tipe }}"
                        data-no-seri="{{ $noSeri }}"
                        data-jenis="{{ $permintaan->jenis_pemeliharaan }}"
                        data-pelaksana-mode="{{ $mode }}"
                        data-vendor="{{ $vendorName }}"
                        data-teknisi="{{ $teknisiName }}"
                        @selected(old('id_permintaan_pemeliharaan', optional($selectedPermintaan)->id_permintaan_pemeliharaan) == $permintaan->id_permintaan_pemeliharaan)
                    >
                        {{ $permintaan->no_permintaan_pemeliharaan }} — {{ $namaBarang }} ({{ $nomorRegister }})
                    </option>
                @endforeach
            </select>
        </div>
        @php
            $selectedInv = $selectedPermintaan?->registerAset?->inventory;
            $selectedNoSeri = trim((string) (
                $selectedPermintaan?->registerAset?->inventoryItem?->no_seri
                ?? ($selectedInv?->no_seri ?? '')
            ));
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Nama Barang yang akan di-service</label>
                <input type="text" id="nama_barang_service" value="{{ old('nama_barang_service', $selectedInv?->dataBarang?->nama_barang ?? '') }}" readonly class="block w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-gray-800" placeholder="Otomatis terisi dari permintaan">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Nomor Register</label>
                <input type="text" id="nomor_register_service" value="{{ old('nomor_register_service', $selectedPermintaan?->registerAset?->nomor_register ?? '') }}" readonly class="block w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-gray-800" placeholder="Otomatis terisi dari permintaan">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Merk</label>
                <input type="text" id="merk_service" value="{{ old('merk_service', $selectedInv?->merk ?? '') }}" readonly class="block w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-gray-800" placeholder="Otomatis terisi dari inventory register">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Tipe</label>
                <input type="text" id="tipe_service" value="{{ old('tipe_service', $selectedInv?->tipe ?? '') }}" readonly class="block w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-gray-800" placeholder="Otomatis terisi dari inventory register">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">No. Seri</label>
                <input type="text" id="no_seri_service" value="{{ old('no_seri_service', $selectedNoSeri) }}" readonly class="block w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-gray-800" placeholder="Otomatis terisi dari inventory register">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium mb-2">Tanggal Servis</label><input type="date" name="tanggal_service" value="{{ old('tanggal_service', now()->toDateString()) }}" required class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Jenis Servis</label><select name="jenis_service" required class="block w-full border border-gray-300 rounded-md px-3 py-2">@foreach(['RUTIN','KALIBRASI','PERBAIKAN','PENGGANTIAN_SPAREPART'] as $jenis)<option value="{{ $jenis }}" @selected(old('jenis_service')===$jenis)>{{ $jenis }}</option>@endforeach</select></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-md border border-gray-200 p-4 bg-gray-50/50">
            <div>
                <label class="block text-sm font-medium mb-2">Jenis Pelaksana <span class="text-red-500">*</span></label>
                <select name="pelaksana_mode" id="pelaksana_mode" required class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white">
                    <option value="INTERNAL" @selected($selectedJenisPelaksana === 'INTERNAL')>Internal (ATEM / IT Support)</option>
                    <option value="EKSTERNAL" @selected($selectedJenisPelaksana === 'EKSTERNAL')>Vendor / Kontrak Service</option>
                </select>
            </div>
            <div id="field-vendor-wrap" class="{{ $selectedJenisPelaksana === 'EKSTERNAL' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium mb-2">Nama Vendor <span class="text-red-500">*</span></label>
                <input type="text" name="vendor" id="vendor_input" value="{{ $defaultVendor }}" class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white" placeholder="Nama vendor / kontraktor">
                @error('vendor')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-teknisi-internal" class="{{ $selectedJenisPelaksana === 'INTERNAL' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium mb-2">Teknisi Internal <span class="text-red-500">*</span></label>
                <select name="teknisi" id="teknisi_select" class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white">
                    <option value="">Pilih Teknisi</option>
                    @foreach($teknisiPegawais as $teknisi)
                        <option value="{{ $teknisi->nama_pegawai }}" @selected($defaultTeknisi === $teknisi->nama_pegawai)>
                            {{ $teknisi->nama_pegawai }}@if($teknisi->masterJabatan) — {{ $teknisi->masterJabatan->nama_jabatan }}@endif
                        </option>
                    @endforeach
                </select>
                @if($teknisiPegawais->isEmpty())
                    <p class="mt-1 text-xs text-amber-600">Belum ada pegawai dengan jabatan ATEM / IT Support. Tambahkan di Master Pegawai.</p>
                @endif
                @error('teknisi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-teknisi-eksternal" class="{{ $selectedJenisPelaksana === 'EKSTERNAL' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium mb-2">Nama Teknisi Vendor</label>
                <input type="text" name="teknisi_eksternal" id="teknisi_eksternal_input" value="{{ $selectedJenisPelaksana === 'EKSTERNAL' ? $defaultTeknisi : '' }}" class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white" placeholder="Nama teknisi pihak luar (opsional)">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium mb-2">Biaya Servis</label><input type="number" step="0.01" min="0" name="biaya_service" value="{{ old('biaya_service', 0) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Biaya Sparepart</label><input type="number" step="0.01" min="0" name="biaya_sparepart" value="{{ old('biaya_sparepart', 0) }}" class="block w-full border border-gray-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-2">Kondisi Setelah Servis</label><select name="kondisi_setelah_service" class="block w-full border border-gray-300 rounded-md px-3 py-2"><option value="">-</option>@foreach(['BAIK','RUSAK_RINGAN','RUSAK_BERAT','TIDAK_BISA_DIPERBAIKI'] as $kondisi)<option value="{{ $kondisi }}" @selected(old('kondisi_setelah_service')===$kondisi)>{{ $kondisi }}</option>@endforeach</select></div>
            <div>
                <label class="block text-sm font-medium mb-2">Rekomendasi <span class="text-red-500">*</span> (wajib jika status SELESAI)</label>
                <select name="rekomendasi" id="rekomendasi_select" class="block w-full border border-gray-300 rounded-md px-3 py-2 @error('rekomendasi') border-red-500 @enderror">
                    <option value="">Pilih rekomendasi</option>
                    @foreach(\App\Enums\PemeliharaanRekomendasi::cases() as $rek)
                        <option value="{{ $rek->value }}" @selected(old('rekomendasi')===$rek->value)>{{ $rek->label() }}</option>
                    @endforeach
                </select>
                @error('rekomendasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Status Service</label>
                <select name="status_service" class="block w-full border border-gray-300 rounded-md px-3 py-2">
                    @foreach(['MENUNGGU','DIPROSES','SELESAI'] as $st)
                        <option value="{{ $st }}" @selected(old('status_service', 'MENUNGGU')===$st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @include('maintenance.service-report._sparepart-table', ['sparepartRows' => collect(old('spareparts', [['nama_sparepart' => '', 'merk' => '', 'nomor_seri' => '']]))])

        <div>
            <label class="block text-sm font-medium mb-2">Deskripsi Kerja</label>
            <textarea name="deskripsi_kerja" id="deskripsi_kerja" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Otomatis dari deskripsi kerusakan permintaan">{{ $defaultDeskripsiKerja }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Terisi dari deskripsi kerusakan / masalah pada permintaan pemeliharaan (dapat diedit).</p>
        </div>
        <div><label class="block text-sm font-medium mb-2">Tindakan</label><textarea name="tindakan_yang_dilakukan" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2">{{ old('tindakan_yang_dilakukan') }}</textarea></div>
        <div>
            <label class="block text-sm font-medium mb-2">Catatan</label>
            <textarea name="catatan" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Catatan servis, rekomendasi, atau keterangan lainnya...">{{ $defaultCatatan }}</textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">File Laporan (Upload)</label>
                <input id="file_laporan_upload_create" type="file" name="file_laporan" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                <label for="file_laporan_upload_create" class="inline-flex items-center px-3 py-2 border border-blue-200 bg-blue-50 text-blue-700 text-sm font-medium rounded-md cursor-pointer hover:bg-blue-100">
                    Pilih File
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-600 text-white">UPLOAD</span>
                </label>
                <p id="file_laporan_upload_create_name" class="mt-1 text-xs text-gray-500">Belum ada file dipilih</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">File Laporan (Open Kamera)</label>
                <input id="file_laporan_kamera_create" type="file" name="file_laporan_kamera" accept="image/*" capture="environment" class="hidden">
                <label for="file_laporan_kamera_create" class="inline-flex items-center px-3 py-2 border border-green-200 bg-green-100 text-green-900 text-sm font-medium rounded-md cursor-pointer hover:bg-green-100">
                    Buka Kamera
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-600 text-white">KAMERA</span>
                </label>
                <p id="file_laporan_kamera_create_name" class="mt-1 text-xs text-gray-500">Belum ada foto diambil</p>
            </div>
        </div>
        <div id="file_laporan_preview_create" class="hidden rounded-md border border-gray-200 bg-gray-50 p-3">
            <p class="text-xs font-medium text-gray-600 mb-2">Preview File Laporan</p>
            <div id="file_laporan_preview_create_content"></div>
        </div>
        <div class="flex justify-end space-x-3"><a href="{{ route('maintenance.service-report.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Batal</a><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button></div>
    </form>
</div>
@push('scripts')
<script>
(function () {
    const PENDING = @json(\App\Enums\PemeliharaanRekomendasi::PendingSparepart->value);
    const deskripsiKerusakanByPermintaan = @json($deskripsiKerusakanByPermintaan);
    const permintaanSelect = document.getElementById('id_permintaan_pemeliharaan');
    const pelaksanaMode = document.getElementById('pelaksana_mode');
    const rekomendasiSelect = document.getElementById('rekomendasi_select');
    const teknisiSelect = document.getElementById('teknisi_select');
    const teknisiEksternal = document.getElementById('teknisi_eksternal_input');
    const deskripsiKerjaInput = document.getElementById('deskripsi_kerja');
    const form = document.getElementById('form-service-report');
    let lastSyncedDeskripsi = deskripsiKerjaInput?.value || '';

    function syncFromPermintaan() {
        if (!permintaanSelect) return;
        const option = permintaanSelect.options[permintaanSelect.selectedIndex];
        const map = {
            nama_barang_service: 'namaBarang',
            nomor_register_service: 'nomorRegister',
            merk_service: 'merk',
            tipe_service: 'tipe',
            no_seri_service: 'noSeri',
        };
        Object.entries(map).forEach(([id, key]) => {
            const el = document.getElementById(id);
            if (el) el.value = option?.dataset?.[key] || '';
        });
        const jenisSelect = document.querySelector('select[name="jenis_service"]');
        if (jenisSelect && option?.dataset?.jenis) {
            if ([...jenisSelect.options].some((o) => o.value === option.dataset.jenis)) {
                jenisSelect.value = option.dataset.jenis;
            }
        }
        if (pelaksanaMode && option?.dataset?.pelaksanaMode) {
            pelaksanaMode.value = option.dataset.pelaksanaMode;
        }
        const vendorInput = document.getElementById('vendor_input');
        if (vendorInput) vendorInput.value = option?.dataset?.vendor || '';
        if (teknisiSelect) teknisiSelect.value = option?.dataset?.teknisi || '';
        if (teknisiEksternal) teknisiEksternal.value = option?.dataset?.pelaksanaMode === 'EKSTERNAL' ? (option?.dataset?.teknisi || '') : '';

        if (deskripsiKerjaInput) {
            const fromPermintaan = deskripsiKerusakanByPermintaan[String(permintaanSelect.value)] || '';
            const current = deskripsiKerjaInput.value || '';
            // Isi ulang jika kosong atau masih sama dengan sync sebelumnya (belum diedit manual).
            if (current.trim() === '' || current === lastSyncedDeskripsi) {
                deskripsiKerjaInput.value = fromPermintaan;
                lastSyncedDeskripsi = fromPermintaan;
            }
        }

        syncPelaksanaUI();
    }

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
        // Server sudah mengabaikan spareparts jika rekomendasi bukan PENDING.
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
        // Pastikan input sparepart (termasuk file) ikut terkirim.
        document.querySelectorAll('#sparepart-section input, #sparepart-section select').forEach((el) => {
            el.disabled = false;
        });
        const eksternal = pelaksanaMode?.value === 'EKSTERNAL';
        if (eksternal && teknisiEksternal && teknisiSelect) {
            teknisiSelect.disabled = true;
            // map eksternal teknisi into teknisi field
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'teknisi';
            hidden.value = teknisiEksternal.value || '';
            form.appendChild(hidden);
        }
    });

    permintaanSelect?.addEventListener('change', syncFromPermintaan);
    pelaksanaMode?.addEventListener('change', syncPelaksanaUI);
    rekomendasiSelect?.addEventListener('change', syncSparepartUI);

    syncFromPermintaan();
    syncPelaksanaUI();
    syncSparepartUI();

    const uploadInput = document.getElementById('file_laporan_upload_create');
    const kameraInput = document.getElementById('file_laporan_kamera_create');
    const uploadName = document.getElementById('file_laporan_upload_create_name');
    const kameraName = document.getElementById('file_laporan_kamera_create_name');
    const previewWrap = document.getElementById('file_laporan_preview_create');
    const previewContent = document.getElementById('file_laporan_preview_create_content');
    function renderPreview(file) {
        if (!previewWrap || !previewContent || !file) return;
        const url = URL.createObjectURL(file);
        const isImage = file.type.startsWith('image/');
        previewContent.innerHTML = isImage
            ? '<img src="' + url + '" alt="Preview" class="max-h-64 rounded border border-gray-200">'
            : '<a href="' + url + '" target="_blank" class="text-sm text-blue-600 underline">' + file.name + '</a>';
        previewWrap.classList.remove('hidden');
    }
    uploadInput?.addEventListener('change', function () {
        uploadName.textContent = this.files?.[0]?.name || 'Belum ada file dipilih';
        if (this.files?.[0]) renderPreview(this.files[0]);
    });
    kameraInput?.addEventListener('change', function () {
        kameraName.textContent = this.files?.[0]?.name || 'Belum ada foto diambil';
        if (this.files?.[0]) renderPreview(this.files[0]);
    });
})();
</script>
@endpush
@endsection
