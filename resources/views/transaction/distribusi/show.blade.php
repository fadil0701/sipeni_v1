@extends('layouts.app')

@section('content')
@php
    $statusValue = $distribusi->status_distribusi instanceof \App\Enums\DistribusiStatus
        ? $distribusi->status_distribusi->value
        : $distribusi->status_distribusi;
    $hasPegawaiPengirim = (bool) $distribusi->id_pegawai_pengirim;
    $sbbkTemplateActive = false;
    if ((bool) config('sipeni.feature_print_templates', false)) {
        $sbbkTemplateActive = \App\Models\PrintTemplate::query()
            ->where('key', 'distribusi.sbbk')
            ->where('is_active', true)
            ->exists();
    }
@endphp

<div class="mb-4">
    <a href="{{ route('transaction.distribusi.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Distribusi
    </a>
</div>


<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Distribusi Barang (SBBK)</h2>
            <p class="text-sm text-gray-600 mt-1">No. SBBK: <span class="font-semibold">{{ $distribusi->no_sbbk }}</span></p>
            <p class="text-xs text-gray-500 mt-1">
                @if($statusValue === 'dikirim')
                    Barang sudah dikirim. Lengkapi bukti sampai (foto + nama penerima) agar status menjadi selesai dan klinik dapat verifikasi.
                @else
                    Lanjutkan alur dengan tombol di kanan: ubah data (draft), proses, lalu kirim setelah pegawai pengirim diisi.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if($sbbkTemplateActive)
                <a
                    href="{{ route('transaction.distribusi.print-sbbk', $distribusi->id_distribusi) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-800 bg-gray-100 rounded-md hover:bg-gray-200 border border-gray-300"
                >
                    Cetak SBBK (template)
                </a>
            @endif
            @if($statusValue === 'draft')
                <a
                    href="{{ route('transaction.distribusi.edit', $distribusi->id_distribusi) }}"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-100 rounded-md hover:bg-amber-200"
                >
                    Edit
                </a>
                <a
                    href="{{ route('transaction.distribusi.edit', ['id' => $distribusi->id_distribusi, 'intent' => 'proses']) }}"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-800 bg-blue-100 rounded-md hover:bg-blue-200"
                >
                    Proses
                </a>
            @endif
            @if($statusValue === 'diproses')
                <a
                    href="{{ route('transaction.distribusi.edit', $distribusi->id_distribusi) }}"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-100 rounded-md hover:bg-amber-200"
                >
                    Edit
                </a>
                @if(!$hasPegawaiPengirim)
                    <a
                        href="{{ route('transaction.distribusi.edit', ['id' => $distribusi->id_distribusi, 'intent' => 'proses']) }}"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-800 bg-blue-100 rounded-md hover:bg-blue-200"
                    >
                        Isi Pegawai Pengirim
                    </a>
                @else
                    <form method="POST" action="{{ route('transaction.distribusi.kirim', $distribusi->id_distribusi) }}" class="inline" data-confirm="Kirim distribusi ini sekarang?">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200"
                        >
                            Kirim
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. SBBK</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->no_sbbk }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $statusColor = \App\Support\UiColor::badgeForStatus($statusValue);
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $statusValue }}
                            </span>
                        </dd>
                    </div>
                    @if($distribusi->permintaan)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Permintaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('transaction.permintaan-barang.show', $distribusi->permintaan->id_permintaan) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $distribusi->permintaan->no_permintaan }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja Pemohon</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->permintaan->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Distribusi</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->tanggal_distribusi->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang Asal</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                // Ambil semua gudang asal dari detail distribusi (karena bisa berbeda per item)
                                $gudangAsalList = $distribusi->detailDistribusi->map(function($detail) {
                                    if ($detail->inventory && $detail->inventory->gudang) {
                                        return $detail->inventory->gudang->nama_gudang;
                                    }
                                    return null;
                                })->filter()->unique()->values();
                                
                                // Jika semua dari gudang yang sama, tampilkan satu
                                // Jika berbeda, tampilkan semua
                                if ($gudangAsalList->count() == 1) {
                                    echo $gudangAsalList->first();
                                } elseif ($gudangAsalList->count() > 1) {
                                    echo $gudangAsalList->join(', ');
                                    echo ' <span class="text-xs text-gray-500">(' . $gudangAsalList->count() . ' gudang)</span>';
                                } else {
                                    // Fallback ke gudang asal dari distribusi
                                    echo $distribusi->gudangAsal->nama_gudang ?? '-';
                                }
                            @endphp
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang Tujuan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->gudangTujuan->nama_gudang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pegawai Pengirim</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $distribusi->pegawaiPengirim->nama_pegawai ?? '-' }}</dd>
                    </div>
                    @if($distribusi->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $distribusi->keterangan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            @php
                $penerimaanAktif = $penerimaanAktif ?? $distribusi->penerimaanBarang->sortByDesc('id_penerimaan')->first();
                $butuhBuktiSampai = $statusValue === 'dikirim'
                    && $penerimaanAktif
                    && $penerimaanAktif->status_penerimaan === 'MENUNGGU_BUKTI_SAMPAI';
                $sudahAdaBukti = $penerimaanAktif && $penerimaanAktif->hasBuktiSampai();
            @endphp

            @if($butuhBuktiSampai)
            <div id="bukti-sampai" class="border border-blue-200 rounded-lg bg-blue-50 p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Bukti Barang Sampai di Tujuan</h3>
                <p class="text-sm text-gray-600 mb-4">Pilih pegawai penerima, lalu unggah foto atau ambil dari kamera (dengan GPS). </p>
                <form method="POST" action="{{ route('transaction.distribusi.bukti-sampai', $distribusi->id_distribusi) }}" enctype="multipart/form-data" class="space-y-4" id="formBuktiSampai" data-confirm="off">
                    @csrf
                    <input type="hidden" name="loc_a" id="gps_latitude" value="{{ old('loc_a') }}">
                    <input type="hidden" name="loc_b" id="gps_longitude" value="{{ old('loc_b') }}">
                    <input type="hidden" name="loc_c" id="gps_akurasi" value="{{ old('loc_c') }}">
                    <input type="hidden" name="loc_d" id="gps_alamat" value="{{ old('loc_d') }}">

                    <div class="space-y-4">
                        {{-- Pegawai: full width di HP --}}
                        <div>
                            <label for="id_pegawai_penerima" class="block text-sm font-medium text-gray-700 mb-1">
                                Pegawai penerima <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="id_pegawai_penerima"
                                name="id_pegawai_penerima"
                                required
                                class="block w-full px-3 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white @error('id_pegawai_penerima') border-red-500 @enderror"
                            >
                                <option value="">Pilih pegawai penerima</option>
                                @forelse(($pegawaiPenerimaOptions ?? collect()) as $pegawai)
                                    <option value="{{ $pegawai->id }}" @selected((string) old('id_pegawai_penerima') === (string) $pegawai->id)>
                                        {{ $pegawai->nama_pegawai }}{{ $pegawai->nip_pegawai ? ' ('.$pegawai->nip_pegawai.')' : '' }}
                                    </option>
                                @empty
                                    <option value="" disabled>Tidak ada pegawai di unit tujuan</option>
                                @endforelse
                            </select>
                            @if(!empty($namaUnitTujuan))
                                <p class="mt-1 text-xs text-gray-500">Daftar pegawai: {{ $namaUnitTujuan }}</p>
                            @endif
                            @error('id_pegawai_penerima')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if(($pegawaiPenerimaOptions ?? collect())->isEmpty())
                                <p class="mt-1 text-sm text-amber-700">Belum ada data pegawai untuk unit kerja tujuan. Tambahkan pegawai di master organisasi terlebih dahulu.</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Foto bukti sampai <span class="text-red-500">*</span></h4>
                            <input type="hidden" name="sumber" id="sumber_bukti_sampai" value="{{ old('sumber', 'upload') }}">

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-6">
                                <div id="panel_upload_bukti" class="rounded-md border border-gray-200 bg-white p-3 sm:p-4">
                                    <label for="foto_bukti_sampai" class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                                    <input
                                        type="file"
                                        id="foto_bukti_sampai"
                                        name="foto"
                                        accept="image/jpeg,image/png,image/webp,image/jpg"
                                        class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 @error('foto') border-red-500 @enderror"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG/WebP, maksimal 5 MB.</p>
                                    @error('foto')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('sumber')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="panel_kamera_bukti" class="rounded-md border border-gray-200 bg-white p-3 sm:p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ambil dari Kamera (+ GPS)</label>
                                    <button type="button" id="btn_buka_kamera_bukti" class="w-full sm:w-auto px-3 py-2.5 sm:py-2 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">Buka Kamera</button>
                                    <p id="kamera_help" class="mt-1 text-xs text-gray-500">Klik "Buka Kamera" untuk mulai memotret. GPS diambil otomatis (butuh HTTPS + izin lokasi browser).</p>
                                    @error('loc_a')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Preview kamera: frame seperti layar HP (portrait 9:16) --}}
                            <div id="panel_preview_kamera" class="mt-4 hidden">
                                <div class="border border-gray-200 rounded-md p-3 sm:p-4 bg-white w-full" style="max-width: 22rem;">
                                    <p class="text-xs font-medium text-gray-600 mb-2">Preview Kamera (mode HP)</p>
                                    <div
                                        class="rounded-md bg-black overflow-hidden mx-auto"
                                        style="width: min(100%, 280px); aspect-ratio: 9 / 16; max-height: 70vh;"
                                    >
                                        <video
                                            id="kameraVideo"
                                            autoplay
                                            playsinline
                                            muted
                                            class="block w-full h-full bg-black"
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                        ></video>
                                    </div>
                                    <canvas id="kameraCanvas" class="hidden"></canvas>
                                    <p id="kameraGpsLive" class="mt-2 text-xs text-gray-700" style="word-break: break-word; white-space: normal;">GPS: menunggu…</p>
                                    <button
                                        type="button"
                                        id="btn_ambil_foto_kamera"
                                        class="mt-3 w-full px-3 py-2.5 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100"
                                    >Ambil Foto</button>
                                </div>
                            </div>

                            {{-- Hasil: hanya tampil setelah upload/capture --}}
                            <div id="panel_hasil_bukti" class="mt-4 hidden">
                                <div class="border border-green-200 rounded-md p-3 sm:p-4 bg-white w-full" style="max-width: 22rem;">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                        <p class="text-xs font-medium text-gray-600">Hasil foto</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" id="btn_buka_foto_bukti" class="px-2 py-1 border border-blue-300 rounded text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">Buka foto</button>
                                            <button type="button" id="btn_ambil_ulang_foto" class="px-2 py-1 border border-amber-300 rounded text-xs font-medium text-amber-800 bg-amber-50 hover:bg-amber-100">Ambil ulang foto</button>
                                        </div>
                                    </div>
                                    <div
                                        class="rounded-md overflow-hidden mx-auto border border-gray-100 bg-black"
                                        style="width: min(100%, 280px); aspect-ratio: 9 / 16; max-height: 70vh;"
                                    >
                                        <img id="foto_bukti_sampai_preview" src="" alt="Preview bukti sampai" class="block w-full h-full" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            </div>

                            <div id="gps_status_box" class="mt-3 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                    <div>
                                        <span class="font-medium text-gray-800">Lokasi GPS:</span>
                                        <span id="gps_status_text" style="word-break: break-word; white-space: normal;">Belum diambil — akan diminta saat buka kamera / upload</span>
                                    </div>
                                    <button type="button" id="btn_ambil_gps" class="shrink-0 px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
                                        Ambil ulang lokasi
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Lokasi dikunci sekali saat buka kamera. Gunakan tombol di atas hanya jika titik tidak sesuai.</p>
                            </div>
                        </div>

                        <div>
                            <label for="catatan_pengirim" class="block text-sm font-medium text-gray-700 mb-1">Catatan pengirim (opsional)</label>
                            <textarea
                                id="catatan_pengirim"
                                name="catatan_pengirim"
                                rows="2"
                                class="block w-full px-3 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: Diterima di lobby klinik pukul 10:00"
                            >{{ old('catatan_pengirim') }}</textarea>
                        </div>
                    </div>
                    <div class="flex justify-stretch sm:justify-end">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700" @disabled(($pegawaiPenerimaOptions ?? collect())->isEmpty())>
                            Simpan Bukti Sampai
                        </button>
                    </div>
                </form>
            </div>
            @elseif($sudahAdaBukti)
            <div class="border border-green-200 rounded-lg bg-green-50 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Bukti Barang Sampai</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama penerima di lokasi</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaanAktif->nama_penerima_lokasi }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Waktu laporkan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ optional($penerimaanAktif->waktu_sampai)->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Sumber bukti</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            {{ ($penerimaanAktif->sumber_bukti_sampai ?? 'upload') === 'kamera' ? 'Kamera + GPS' : 'Unggah file' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Lokasi GPS</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @if($penerimaanAktif->gps_latitude && $penerimaanAktif->gps_longitude)
                                @if(filled($penerimaanAktif->gps_alamat))
                                    <div class="mb-1">{{ $penerimaanAktif->gps_alamat }}</div>
                                @endif
                                <div class="text-xs font-normal text-gray-600">
                                    {{ number_format((float) $penerimaanAktif->gps_latitude, 6, '.', '') }},
                                    {{ number_format((float) $penerimaanAktif->gps_longitude, 6, '.', '') }}
                                    @if($penerimaanAktif->gps_akurasi)
                                        <span class="text-gray-500">(±{{ number_format((float) $penerimaanAktif->gps_akurasi, 0) }} m)</span>
                                    @endif
                                    <a
                                        href="https://www.google.com/maps?q={{ $penerimaanAktif->gps_latitude }},{{ $penerimaanAktif->gps_longitude }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="ml-2 text-sm font-medium text-blue-700 hover:text-blue-900"
                                    >Buka peta</a>
                                </div>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    @if($penerimaanAktif->catatan_pengirim)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Catatan pengirim</dt>
                        <dd class="text-sm text-gray-900">{{ $penerimaanAktif->catatan_pengirim }}</dd>
                    </div>
                    @endif
                    @if($penerimaanAktif->foto_bukti_sampai)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Foto bukti</dt>
                        <dd>
                            <div class="flex flex-wrap items-start gap-3">
                                <img
                                    src="{{ route('media.show', ['path' => $penerimaanAktif->foto_bukti_sampai]) }}"
                                    alt="Foto bukti sampai"
                                    class="max-h-64 rounded-lg border border-gray-200 object-contain bg-white"
                                >
                                <button
                                    type="button"
                                    class="js-open-bukti-foto inline-flex items-center gap-1.5 px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                                    data-foto-url="{{ route('media.show', ['path' => $penerimaanAktif->foto_bukti_sampai]) }}"
                                >
                                    Buka foto
                                </button>
                            </div>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            {{-- Lightbox dipindah ke body via JS agar tidak terpotong overflow layout --}}
            <div id="buktiFotoLightbox" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-label="Pratinjau foto bukti" style="display: none;">
                <button type="button" id="buktiFotoLightboxClose" class="absolute top-4 right-4 rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-800 shadow hover:bg-gray-100">Tutup</button>
                <img id="buktiFotoLightboxImg" src="" alt="Foto bukti sampai" class="max-h-[90vh] max-w-[95vw] rounded-lg object-contain shadow-xl bg-white">
            </div>

            <!-- Detail Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Distribusi ({{ $distribusi->detailDistribusi->count() }} item)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Merk</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Distribusi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                @if($distribusi->detailDistribusi->whereIn('inventory.gudang.kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Batch</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exp Date</th>
                                @endif
                                @if($distribusi->detailDistribusi->where('inventory.gudang.kategori_gudang', 'ASET')->count() > 0)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Seri</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php 
                                $total = 0;
                                $hasFarmasiPersediaan = $distribusi->detailDistribusi->whereIn('inventory.gudang.kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0;
                                $hasAset = $distribusi->detailDistribusi->where('inventory.gudang.kategori_gudang', 'ASET')->count() > 0;
                            @endphp
                            @foreach($distribusi->detailDistribusi as $index => $detail)
                            @php 
                                $total += $detail->subtotal;
                                $inventory = $detail->inventory;
                                $kategoriGudang = $inventory->gudang->kategori_gudang ?? null;
                                $isAset = $kategoriGudang === 'ASET';
                                $isFarmasiPersediaan = in_array($kategoriGudang, ['FARMASI', 'PERSEDIAAN']);
                                
                                // Untuk ASET, ambil nomor seri dari inventory_item
                                $noSeriList = [];
                                if ($isAset) {
                                    $inventoryItems = \App\Models\InventoryItem::where('id_inventory', $inventory->id_inventory)
                                        ->where('id_gudang', $inventory->id_gudang)
                                        ->where('status_item', 'AKTIF')
                                        ->limit((int)$detail->qty_distribusi)
                                        ->get();
                                    $noSeriList = $inventoryItems->pluck('no_seri')->filter()->unique()->values();
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $inventory->dataBarang->nama_barang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ trim((string) ($inventory->merk ?? '')) !== '' ? $inventory->merk : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ trim((string) ($inventory->tipe ?? '')) !== '' ? $inventory->tipe : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($detail->qty_distribusi, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                @if($hasFarmasiPersediaan)
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($isFarmasiPersediaan)
                                        {{ $inventory->no_batch ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($isFarmasiPersediaan && $inventory->tanggal_kedaluwarsa)
                                        {{ \Carbon\Carbon::parse($inventory->tanggal_kedaluwarsa)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @endif
                                @if($hasAset)
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($isAset)
                                        @if($noSeriList->count() > 0)
                                            @if($noSeriList->count() <= 3)
                                                {{ $noSeriList->join(', ') }}
                                            @else
                                                {{ $noSeriList->take(3)->join(', ') }}<br>
                                                <span class="text-xs text-gray-500">+{{ $noSeriList->count() - 3 }} lainnya</span>
                                            @endif
                                        @else
                                            {{ $inventory->no_seri ?? '-' }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3 text-sm text-gray-900">Rp {{ number_format($detail->harga_satuan, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">Rp {{ number_format($detail->subtotal, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        {{-- Total di tfoot: enhanceTable (layouts/app) hanya mem-sort tbody.rows, bukan tfoot. --}}
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold border-t border-gray-200">
                                @php
                                    $colspanLabel = 5 + ($hasFarmasiPersediaan ? 2 : 0) + ($hasAset ? 1 : 0);
                                @endphp
                                <td colspan="{{ $colspanLabel }}" class="px-4 py-3 text-sm text-gray-900 text-right">Total</td>
                                <td class="px-4 py-3 text-sm text-gray-900"></td>
                                <td class="px-4 py-3 text-sm text-gray-900">Rp {{ number_format($total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const lightbox = document.getElementById('buktiFotoLightbox');
    const lightboxImg = document.getElementById('buktiFotoLightboxImg');
    const lightboxClose = document.getElementById('buktiFotoLightboxClose');
    const input = document.getElementById('foto_bukti_sampai');
    const previewImg = document.getElementById('foto_bukti_sampai_preview');
    const btnBukaFoto = document.getElementById('btn_buka_foto_bukti');
    const btnAmbilUlang = document.getElementById('btn_ambil_ulang_foto');
    const sumberInput = document.getElementById('sumber_bukti_sampai');
    const gpsLat = document.getElementById('gps_latitude');
    const gpsLng = document.getElementById('gps_longitude');
    const gpsAcc = document.getElementById('gps_akurasi');
    const gpsAlamatInput = document.getElementById('gps_alamat');
    const gpsStatusText = document.getElementById('gps_status_text');
    const btnAmbilGps = document.getElementById('btn_ambil_gps');
    const reverseGeocodeUrl = @json(route('api.geocode.reverse'));
    let reverseGeocodePending = null;
    const panelPreviewKamera = document.getElementById('panel_preview_kamera');
    const panelHasil = document.getElementById('panel_hasil_bukti');
    const kameraVideo = document.getElementById('kameraVideo');
    const kameraCanvas = document.getElementById('kameraCanvas');
    const kameraGpsLive = document.getElementById('kameraGpsLive');
    const kameraHelp = document.getElementById('kamera_help');
    const btnBukaKamera = document.getElementById('btn_buka_kamera_bukti');
    const btnAmbilFoto = document.getElementById('btn_ambil_foto_kamera');
    const form = document.getElementById('formBuktiSampai');

    let objectUrl = null;
    let mediaStream = null;
    let watchId = null;
    let lastGps = null;
    let gpsPending = null;
    let gpsLocked = false;

    if (lightbox && lightbox.parentElement !== document.body) {
        document.body.appendChild(lightbox);
    }

    function openBuktiFoto(url) {
        if (!lightbox || !lightboxImg || !url) return;
        lightboxImg.src = url;
        lightbox.style.display = 'flex';
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeBuktiFoto() {
        if (!lightbox || !lightboxImg) return;
        lightbox.style.display = 'none';
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        lightboxImg.removeAttribute('src');
        document.body.style.overflow = '';
    }

    function setSumber(value) {
        if (sumberInput) sumberInput.value = value;
    }

    function updateGpsUi(pos, options) {
        const keepAlamat = options && options.keepAlamat === true && lastGps && lastGps.alamat;
        lastGps = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
            acc: pos.coords.accuracy || 0,
            alamat: keepAlamat ? lastGps.alamat : null
        };
        if (gpsLat) gpsLat.value = String(lastGps.lat);
        if (gpsLng) gpsLng.value = String(lastGps.lng);
        if (gpsAcc) gpsAcc.value = String(lastGps.acc);
        if (!keepAlamat && gpsAlamatInput) gpsAlamatInput.value = '';
        gpsLocked = true;
        renderGpsStatus();
        resolvePlaceName(lastGps.lat, lastGps.lng);
    }

    function renderGpsStatus() {
        if (!lastGps) return;
        const coord = lastGps.lat.toFixed(6) + ', ' + lastGps.lng.toFixed(6)
            + ' (±' + Math.round(lastGps.acc) + ' m)';
        const lockHint = gpsLocked ? ' [terkunci]' : '';
        const text = lastGps.alamat
            ? (lastGps.alamat + ' — ' + coord + lockHint)
            : (coord + lockHint);
        if (gpsStatusText) gpsStatusText.textContent = text;
        if (kameraGpsLive) {
            kameraGpsLive.textContent = lastGps.alamat
                ? ('Lokasi terkunci: ' + lastGps.alamat)
                : ('GPS terkunci: ' + coord);
        }
    }

    function resolvePlaceName(lat, lng) {
        const url = reverseGeocodeUrl
            + (reverseGeocodeUrl.indexOf('?') >= 0 ? '&' : '?')
            + 'a=' + encodeURIComponent(lat)
            + '&b=' + encodeURIComponent(lng);

        reverseGeocodePending = fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-Sipeni-Silent': '1'
            },
            credentials: 'same-origin',
            sipeniSilent: true
        })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) {
                if (!data || !data.success || !data.alamat) return;
                if (!lastGps) return;
                if (Math.abs(lastGps.lat - lat) > 0.00001 || Math.abs(lastGps.lng - lng) > 0.00001) return;
                lastGps.alamat = data.alamat;
                if (gpsAlamatInput) gpsAlamatInput.value = data.alamat;
                renderGpsStatus();
            })
            .catch(function () { /* nama tempat opsional */ })
            .finally(function () { reverseGeocodePending = null; });
    }

    function gpsErrorMessage(err) {
        if (!err) return 'lokasi gagal diambil';
        if (err.code === 1) {
            return 'izin lokasi ditolak — reset izin situs di browser, pastikan HTTPS, '
                + 'dan SECURITY_PERMISSIONS_POLICY memakai geolocation=(self)';
        }
        if (err.code === 2) return 'posisi tidak tersedia (aktifkan Location Services OS)';
        if (err.code === 3) return 'waktu tunggu GPS habis — coba lagi di tempat terbuka';
        return err.message || 'gagal mengambil lokasi';
    }

    function isGpsPolicyBlocked() {
        try {
            if (typeof document.featurePolicy !== 'undefined'
                && typeof document.featurePolicy.allowsFeature === 'function') {
                return !document.featurePolicy.allowsFeature('geolocation');
            }
            if (typeof document.permissionsPolicy !== 'undefined'
                && typeof document.permissionsPolicy.allowsFeature === 'function') {
                return !document.permissionsPolicy.allowsFeature('geolocation');
            }
        } catch (e) {}
        return false;
    }

    function requestPosition(options) {
        return new Promise(function (resolve, reject) {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation tidak didukung'));
                return;
            }
            if (isGpsPolicyBlocked()) {
                reject({
                    code: 1,
                    message: 'Permissions-Policy memblokir geolocation di header HTTP'
                });
                return;
            }
            if (!window.isSecureContext
                && location.hostname !== 'localhost'
                && location.hostname !== '127.0.0.1') {
                reject({
                    code: 1,
                    message: 'Butuh HTTPS (secure context) untuk GPS'
                });
                return;
            }
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });
    }

    // Ambil GPS sekali lalu kunci. opts.force=true hanya dari tombol "Ambil ulang lokasi".
    async function startGps(opts) {
        const force = !!(opts && opts.force);

        if (!navigator.geolocation) {
            if (gpsStatusText) gpsStatusText.textContent = 'Perangkat tidak mendukung GPS/lokasi';
            if (kameraGpsLive) kameraGpsLive.textContent = 'GPS tidak tersedia';
            return null;
        }

        // Sudah terkunci: jangan refresh otomatis saat perangkat bergeser
        if (gpsLocked && lastGps && !force) {
            renderGpsStatus();
            return lastGps;
        }

        if (gpsPending) return gpsPending;

        stopGpsWatch();

        if (gpsStatusText) {
            gpsStatusText.textContent = force
                ? 'Mengambil ulang lokasi…'
                : 'Mengambil lokasi… izinkan jika browser meminta';
        }
        if (kameraGpsLive) kameraGpsLive.textContent = 'GPS: mengambil lokasi…';

        gpsPending = (async function () {
            try {
                const hi = await requestPosition({
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: force ? 0 : 30000
                });
                updateGpsUi(hi);
                return lastGps;
            } catch (e1) {
                try {
                    const lo = await requestPosition({
                        enableHighAccuracy: false,
                        timeout: 25000,
                        maximumAge: force ? 0 : 120000
                    });
                    updateGpsUi(lo);
                    return lastGps;
                } catch (e2) {
                    const msg = gpsErrorMessage(e2);
                    if (gpsStatusText) gpsStatusText.textContent = 'Gagal GPS: ' + msg;
                    if (kameraGpsLive) kameraGpsLive.textContent = 'GPS gagal: ' + msg;
                    return null;
                }
            } finally {
                gpsPending = null;
            }
        })();

        return gpsPending;
    }

    function stopGpsWatch() {
        if (watchId !== null && navigator.geolocation) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
    }

    function showHasil(blobOrFile) {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        objectUrl = URL.createObjectURL(blobOrFile);
        if (previewImg) {
            previewImg.src = objectUrl;
            previewImg.classList.remove('hidden');
        }
        panelHasil?.classList.remove('hidden');
    }

    function hideHasil() {
        panelHasil?.classList.add('hidden');
        if (previewImg) {
            previewImg.removeAttribute('src');
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        if (input) input.value = '';
    }

    function assignFileToInput(file) {
        if (!input) return;
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) {
            console.warn('DataTransfer tidak didukung', e);
        }
        showHasil(file);
    }

    function showKameraPreview() {
        panelPreviewKamera?.classList.remove('hidden');
        if (btnBukaKamera) btnBukaKamera.classList.add('hidden');
        if (btnAmbilFoto) {
            btnAmbilFoto.classList.remove('hidden');
            btnAmbilFoto.disabled = false;
        }
        if (kameraHelp) kameraHelp.textContent = 'Kamera aktif. Gunakan tombol "Ambil Foto" di bawah preview.';
    }

    function hideKameraPreview() {
        panelPreviewKamera?.classList.add('hidden');
        if (btnBukaKamera) btnBukaKamera.classList.remove('hidden');
        if (btnAmbilFoto) {
            btnAmbilFoto.classList.add('hidden');
            btnAmbilFoto.disabled = true;
        }
    }

    async function openKamera() {
        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            alert('Kamera dan GPS membutuhkan HTTPS. Gunakan localhost atau domain HTTPS.');
            return;
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Browser tidak mendukung kamera. Gunakan upload foto.');
            return;
        }

        // Sembunyikan hasil lama saat buka kamera lagi
        hideHasil();
        startGps();

        try {
            if (mediaStream) {
                mediaStream.getTracks().forEach(function (t) { t.stop(); });
                mediaStream = null;
            }
            // Resolusi seperti HP: portrait 9:16 (ideal 720x1280)
            const phoneConstraints = {
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 720, max: 1080 },
                    height: { ideal: 1280, max: 1920 },
                    aspectRatio: { ideal: 9 / 16 }
                }
            };
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia(phoneConstraints);
            } catch (e) {
                try {
                    // Fallback: tetap portrait jika perangkat mendukung
                    mediaStream = await navigator.mediaDevices.getUserMedia({
                        audio: false,
                        video: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 480 },
                            height: { ideal: 854 }
                        }
                    });
                } catch (e2) {
                    // Laptop webcam (landscape) — preview tetap di frame HP
                    mediaStream = await navigator.mediaDevices.getUserMedia({
                        audio: false,
                        video: true
                    });
                }
            }
            showKameraPreview();
            if (kameraVideo) {
                kameraVideo.srcObject = mediaStream;
                await kameraVideo.play();
            }
            if (kameraHelp) kameraHelp.textContent = 'Kamera aktif. Tunggu GPS jika perlu, lalu klik "Ambil Foto" di bawah preview.';
        } catch (err) {
            hideKameraPreview();
            if (kameraHelp) kameraHelp.textContent = 'Gagal mengakses kamera. Pastikan izin kamera sudah diaktifkan.';
            alert('Tidak dapat membuka kamera: ' + (err && err.message ? err.message : 'izin ditolak'));
        }
    }

    function stopKamera() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(function (t) { t.stop(); });
            mediaStream = null;
        }
        if (kameraVideo) {
            kameraVideo.srcObject = null;
        }
        hideKameraPreview();
        if (kameraHelp) kameraHelp.textContent = 'Klik "Buka Kamera" untuk mulai memotret. GPS akan diambil otomatis.';
    }

    async function ambilFotoDariKamera() {
        if (!mediaStream || !kameraVideo || !kameraCanvas) return;

        // Tunggu GPS sebentar jika belum ada
        if (!lastGps) {
            if (kameraHelp) kameraHelp.textContent = 'Menunggu lokasi GPS…';
            await startGps();
        }
        if (reverseGeocodePending) {
            try { await reverseGeocodePending; } catch (e) {}
        }

        const srcW = kameraVideo.videoWidth || 720;
        const srcH = kameraVideo.videoHeight || 1280;

        // Output seperti foto HP: portrait max 720x1280
        const targetW = 720;
        const targetH = 1280;
        let drawW = srcW;
        let drawH = srcH;
        let cropX = 0;
        let cropY = 0;

        const srcRatio = srcW / srcH;
        const phoneRatio = targetW / targetH; // 9:16

        if (srcRatio > phoneRatio) {
            // Sumber lebih lebar (landscape) — crop tengah ke portrait
            drawH = srcH;
            drawW = Math.round(srcH * phoneRatio);
            cropX = Math.round((srcW - drawW) / 2);
            cropY = 0;
        } else if (srcRatio < phoneRatio) {
            // Sumber lebih tinggi — crop vertikal tengah
            drawW = srcW;
            drawH = Math.round(srcW / phoneRatio);
            cropX = 0;
            cropY = Math.round((srcH - drawH) / 2);
        }

        kameraCanvas.width = targetW;
        kameraCanvas.height = targetH;
        const ctx = kameraCanvas.getContext('2d');
        ctx.drawImage(kameraVideo, cropX, cropY, drawW, drawH, 0, 0, targetW, targetH);

        const w = targetW;
        const h = targetH;

        const pad = Math.max(12, Math.round(w * 0.02));
        let line1;
        let line2;
        if (lastGps) {
            if (lastGps.alamat) {
                line1 = lastGps.alamat.length > 48 ? lastGps.alamat.slice(0, 45) + '…' : lastGps.alamat;
                line2 = lastGps.lat.toFixed(5) + ', ' + lastGps.lng.toFixed(5)
                    + ' (±' + Math.round(lastGps.acc) + ' m) | ' + new Date().toLocaleString('id-ID');
            } else {
                line1 = 'GPS: ' + lastGps.lat.toFixed(6) + ', ' + lastGps.lng.toFixed(6);
                line2 = 'Akurasi ±' + Math.round(lastGps.acc) + ' m | ' + new Date().toLocaleString('id-ID');
            }
        } else {
            line1 = 'GPS: tidak tersedia';
            line2 = 'Izinkan lokasi browser / Location Services | ' + new Date().toLocaleString('id-ID');
        }
        const fontSize = Math.max(14, Math.round(w * 0.028));
        ctx.font = 'bold ' + fontSize + 'px sans-serif';
        const boxH = fontSize * 2.8 + pad;
        ctx.fillStyle = 'rgba(0,0,0,0.55)';
        ctx.fillRect(0, h - boxH, w, boxH);
        ctx.fillStyle = '#ffffff';
        ctx.fillText(line1, pad, h - boxH + fontSize + 4);
        ctx.font = (fontSize * 0.85) + 'px sans-serif';
        ctx.fillText(line2, pad, h - pad);

        kameraCanvas.toBlob(function (blob) {
            if (!blob) {
                alert('Gagal mengambil foto dari kamera.');
                return;
            }
            const file = new File([blob], 'foto-kedatangan-' + Date.now() + '.jpg', { type: 'image/jpeg' });
            assignFileToInput(file);
            setSumber('kamera');
            stopKamera();
            if (kameraHelp) kameraHelp.textContent = 'Foto berhasil. Jika kurang sesuai, klik "Ambil ulang foto".';
            if (!lastGps) {
                if (gpsStatusText) {
                    gpsStatusText.textContent = 'GPS tidak tersedia — foto tetap disimpan. Aktifkan lokasi untuk data GPS.';
                }
            }
        }, 'image/jpeg', 0.92);
    }

    function ambilUlangFoto() {
        hideHasil();
        setSumber('kamera');
        openKamera();
    }

    lightboxClose?.addEventListener('click', function (e) {
        e.preventDefault();
        closeBuktiFoto();
    });
    lightbox?.addEventListener('click', function (e) {
        if (e.target === lightbox) closeBuktiFoto();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeBuktiFoto();
    });

    document.querySelectorAll('.js-open-bukti-foto').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openBuktiFoto(btn.getAttribute('data-foto-url'));
        });
    });

    input?.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) {
            hideHasil();
            return;
        }
        stopKamera();
        setSumber('upload');
        showHasil(file);
        startGps({ force: false });
    });

    btnBukaFoto?.addEventListener('click', function (e) {
        e.preventDefault();
        if (objectUrl) openBuktiFoto(objectUrl);
        else if (previewImg?.src) openBuktiFoto(previewImg.src);
        else alert('Belum ada foto untuk dibuka.');
    });

    btnAmbilUlang?.addEventListener('click', function (e) {
        e.preventDefault();
        ambilUlangFoto();
    });

    btnBukaKamera?.addEventListener('click', function (e) {
        e.preventDefault();
        openKamera();
    });
    btnAmbilFoto?.addEventListener('click', function (e) {
        e.preventDefault();
        ambilFotoDariKamera();
    });
    btnAmbilGps?.addEventListener('click', function (e) {
        e.preventDefault();
        startGps({ force: true });
    });

    form?.addEventListener('submit', function (e) {
        const hasFile = input && input.files && input.files.length > 0;
        if (!hasFile) {
            e.preventDefault();
            alert('Unggah foto atau ambil foto dari kamera terlebih dahulu.');
        }
    });

    window.addEventListener('beforeunload', function () {
        stopKamera();
        stopGpsWatch();
    });
})();
</script>
@endpush



