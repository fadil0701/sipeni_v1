@extends('layouts.app')

@section('content')
<!-- Breadcrumb Navigation -->
<div class="mb-4">
    <a href="{{ route('master.unit-kerja.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Unit Kerja
    </a>
</div>

<!-- Form Card -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Unit Kerja</h2>
    </div>
    
    <form action="{{ route('master.unit-kerja.update', $unitKerja->id_unit_kerja) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Kode Unit Kerja -->
            <div>
                <label for="kode_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Unit Kerja <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="kode_unit_kerja" 
                    name="kode_unit_kerja" 
                    required
                    value="{{ old('kode_unit_kerja', $unitKerja->kode_unit_kerja) }}"
                    placeholder="Masukkan kode unit kerja"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kode_unit_kerja') border-red-500 @enderror"
                >
                @error('kode_unit_kerja')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Unit Kerja -->
            <div>
                <label for="nama_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Unit Kerja <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama_unit_kerja" 
                    name="nama_unit_kerja" 
                    required
                    value="{{ old('nama_unit_kerja', $unitKerja->nama_unit_kerja) }}"
                    placeholder="Masukkan nama unit kerja"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_unit_kerja') border-red-500 @enderror"
                >
                @error('nama_unit_kerja')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kota/Kabupaten -->
            <div>
                <label for="kota_kabupaten" class="block text-sm font-medium text-gray-700 mb-2">
                    Kota/Kabupaten<span class="text-red-500">*</span>
                </label>
                <select
                    id="kota_kabupaten"
                    name="kota_kabupaten"
                    required
                    data-searchable="false"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kota_kabupaten') border-red-500 @enderror"
                >
                    <option value="">Pilih Kota/Kabupaten</option>
                    @foreach($wilayahDki as $kotaKabupaten => $kecamatans)
                        <option value="{{ $kotaKabupaten }}" @selected(old('kota_kabupaten', $unitKerja->kota_kabupaten) === $kotaKabupaten)>{{ $kotaKabupaten }}</option>
                    @endforeach
                </select>
                @error('kota_kabupaten')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kecamatan -->
            <div>
                <label for="kecamatan" class="block text-sm font-medium text-gray-700 mb-2">
                    Kecamatan <span class="text-red-500">*</span>
                </label>
                @php
                    $kotaKabupatenValue = old('kota_kabupaten', $unitKerja->kota_kabupaten);
                    $selectedKecamatanServer = old('kecamatan', $unitKerja->kecamatan);
                    $kecamatanOptions = [];
                    $normalizeText = function ($value) {
                        $value = (string) ($value ?? '');
                        // Tangani NBSP + whitespace variasi (termasuk spasi ganda/berulang).
                        $value = preg_replace('/[\x{00A0}\x{2007}\x{202F}\x{FEFF}]+/u', ' ', $value);
                        $value = preg_replace('/\x{200B}/u', '', $value); // zero-width space
                        $value = preg_replace('/\s+/u', ' ', $value);
                        $value = trim($value);
                        return mb_strtoupper($value, 'UTF-8');
                    };

                    $selectedNorm = $normalizeText($kotaKabupatenValue);
                    foreach ($wilayahDki as $kotaKabupaten => $list) {
                        if ($normalizeText($kotaKabupaten) === $selectedNorm) {
                            $kecamatanOptions = $list;
                            break;
                        }
                    }
                @endphp
                <select
                    id="kecamatan"
                    name="kecamatan"
                    required
                    data-searchable="false"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kecamatan') border-red-500 @enderror"
                >
                    <option value="">Pilih Kecamatan</option>
                    @foreach($kecamatanOptions as $kecamatan)
                        <option value="{{ $kecamatan }}" @selected($selectedKecamatanServer === $kecamatan)>
                            {{ $kecamatan }}
                        </option>
                    @endforeach
                </select>
                @error('kecamatan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('master.unit-kerja.index') }}" 
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wilayahDki = @json($wilayahDki);
        // Normalisasi agar pencocokan key tidak gagal karena perbedaan spasi/case (termasuk NBSP).
        const normalizeText = function (value) {
            // Normalisasi untuk berbagai jenis whitespace (NBSP/NNBSP/zero-width).
            // Tujuannya agar pencocokan key tidak gagal karena karakter tak terlihat.
            let v = String(value ?? '');
            v = v
                .replace(/[\u00A0\u2007\u202F\u200B\uFEFF]/g, ' ')
                .replace(/\u200B/g, '') // zero-width space
                .replace(/\s+/g, ' ')
                .trim();
            return v.toUpperCase();
        };
        const kotaSelect = document.getElementById('kota_kabupaten');
        const kecamatanSelect = document.getElementById('kecamatan');
        const selectedKecamatan = @json(old('kecamatan', $unitKerja->kecamatan));

        function renderKecamatanOptions(selectedKota, currentKecamatan) {
            const normalizedKota = normalizeText(selectedKota);
            let list = [];
            // Hindari lookup berbasis key yang rapuh; cari dengan iterasi + normalisasi.
            const normalizedNoSpace = normalizedKota.replace(/\s+/g, '');
            Object.entries(wilayahDki || {}).some(function ([kota, kecamatans]) {
                const kotaNorm = normalizeText(kota);
                const kotaNoSpace = kotaNorm.replace(/\s+/g, '');
                const matchExact = kotaNorm === normalizedKota;
                const matchNoSpace = kotaNoSpace === normalizedNoSpace;
                const matchContains = kotaNorm.includes(normalizedKota) || normalizedKota.includes(kotaNorm);
                if (matchExact || matchNoSpace || matchContains) {
                    list = Array.isArray(kecamatans) ? kecamatans : [];
                    return true;
                }
                return false;
            });

            // Kalau key tidak ketemu (akibat mismatch string), jangan kosongkan opsi
            // supaya fallback server-side tetap tampil.
            if (!list || !list.length) {
                return;
            }

            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            list.forEach(function (kecamatan) {
                const option = document.createElement('option');
                option.value = kecamatan;
                option.textContent = kecamatan;
                if (currentKecamatan && currentKecamatan === kecamatan) {
                    option.selected = true;
                }
                kecamatanSelect.appendChild(option);
            });
        }

        kotaSelect.addEventListener('change', function () {
            renderKecamatanOptions(this.value, null);
        });

        if (kotaSelect.value) {
            renderKecamatanOptions(kotaSelect.value, selectedKecamatan);
        }

        // Fallback server-side (minimalkan kondisi jika JS tidak jalan / key tidak match)
        // -> diisi dari variabel PHP yang sudah ada (wilayahDki). Jika key tidak match,
        // maka opsi tetap kosong seperti sebelumnya.
    });
</script>
@endsection

