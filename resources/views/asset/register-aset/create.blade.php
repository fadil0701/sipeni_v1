@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.register-aset.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Register Aset
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Register Aset</h2>
        <p class="mt-1 text-sm text-gray-600">Buat register aset baru secara manual</p>
    </div>
    
    <form action="{{ route('asset.register-aset.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Inventory Item -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Inventory Item</h3>
                <div>
                    <label for="id_inventory" class="block text-sm font-medium text-gray-700 mb-2">
                        Inventory Item (ASET) <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_item" 
                        name="id_item" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_item') border-red-500 @enderror"
                        onchange="generateNomorRegister()"
                    >
                        <option value="">Pilih Inventory Item</option>
                        @if(isset($inventoryItems) && $inventoryItems->isNotEmpty())
                            @foreach($inventoryItems as $item)
                                <option 
                                    value="{{ $item->id_item ?? '' }}" 
                                    data-kode-register="{{ $item->kode_register ?? '' }}"
                                    data-id-inventory="{{ $item->id_inventory ?? '' }}"
                                    {{ old('id_item') == $item->id_item ? 'selected' : '' }}
                                >
                                    {{ $item->kode_register ?? 'NO-REGISTER' }} - {{ $item->inventory->dataBarang->nama_barang ?? 'Nama Barang' }} 
                                    @if($item->gudang && $item->gudang->nama_gudang)
                                        ({{ $item->gudang->nama_gudang }})
                                    @elseif($item->inventory && $item->inventory->gudang && $item->inventory->gudang->nama_gudang)
                                        ({{ $item->inventory->gudang->nama_gudang }})
                                    @endif
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>Tidak ada inventory item tersedia</option>
                        @endif
                    </select>
                    <input type="hidden" id="id_inventory" name="id_inventory" value="">
                    @error('id_item')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('id_inventory')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih inventory item (hasil auto add row) yang akan dibuat register aset</p>
                    @if($inventoryItems->isEmpty())
                        <p class="mt-2 text-sm text-yellow-600 bg-yellow-50 p-3 rounded-md border border-yellow-200">
                            <strong>Perhatian:</strong> Tidak ada inventory item tersedia. Pastikan sudah ada Data Inventory dengan jenis ASET yang sudah dibuat InventoryItem secara otomatis.
                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-500">Total: <strong>{{ $inventoryItems->count() }}</strong> item tersedia</p>
                    @endif
                </div>
            </div>

            <!-- Informasi Unit Kerja & Ruangan -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Unit Kerja & Ruangan</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_unit_kerja" 
                            name="id_unit_kerja" 
                            required
                            onchange="filterRuangan(this.value)"
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
                        <label for="id_ruangan" class="block text-sm font-medium text-gray-700 mb-2">
                            Ruangan <span class="text-gray-500">(Opsional)</span>
                        </label>
                        <select 
                            id="id_ruangan" 
                            name="id_ruangan" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Ruangan</option>
                            @foreach($ruangans as $ruangan)
                                <option 
                                    value="{{ $ruangan->id_ruangan }}" 
                                    data-unit-kerja="{{ $ruangan->id_unit_kerja }}"
                                    {{ old('id_ruangan') == $ruangan->id_ruangan ? 'selected' : '' }}
                                >
                                    {{ $ruangan->nama_ruangan }} ({{ $ruangan->unitKerja->nama_unit_kerja ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_ruangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Pilih ruangan jika aset akan ditempatkan di ruangan tertentu</p>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="nomor_register" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Register <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 font-normal">(Otomatis di-generate)</span>
                    </label>
                    <input 
                        type="text" 
                        id="nomor_register" 
                        name="nomor_register" 
                        value="{{ old('nomor_register') }}"
                        readonly
                        placeholder="Akan di-generate otomatis setelah memilih Unit Kerja dan Ruangan"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nomor_register') border-red-500 @enderror"
                    >
                    @error('nomor_register')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Format: [KODE_REGISTER]/[ID_UNIT_KERJA]/[ID_RUANGAN]/[URUT] atau [KODE_REGISTER]/[ID_UNIT_KERJA]/[URUT] jika tidak ada ruangan</p>
                </div>

                <div>
                    <label for="kondisi_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Kondisi Aset <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="kondisi_aset" 
                        name="kondisi_aset" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kondisi_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Kondisi</option>
                        <option value="BAIK" {{ old('kondisi_aset') == 'BAIK' ? 'selected' : '' }}>Baik</option>
                        <option value="RUSAK_RINGAN" {{ old('kondisi_aset') == 'RUSAK_RINGAN' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="RUSAK_BERAT" {{ old('kondisi_aset') == 'RUSAK_BERAT' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Status Aset <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status_aset" 
                        name="status_aset" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Status</option>
                        <option value="AKTIF" {{ old('status_aset', 'AKTIF') == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                        <option value="NONAKTIF" {{ old('status_aset') == 'NONAKTIF' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_perolehan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Perolehan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_perolehan" 
                        name="tanggal_perolehan" 
                        value="{{ old('tanggal_perolehan', date('Y-m-d')) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_perolehan') border-red-500 @enderror"
                    >
                    @error('tanggal_perolehan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Tanggal ketika aset diperoleh</p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('asset.register-aset.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Register Aset
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function generateNomorRegister() {
    const itemSelect = document.getElementById('id_item');
    const inventoryHidden = document.getElementById('id_inventory');
    const unitKerjaSelect = document.getElementById('id_unit_kerja');
    const ruanganSelect = document.getElementById('id_ruangan');
    const nomorRegisterInput = document.getElementById('nomor_register');
    
    if (!itemSelect || !unitKerjaSelect || !nomorRegisterInput) return;
    
    const itemValue = itemSelect.value;
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const inventoryId = selectedOption ? selectedOption.dataset.idInventory : '';
    
    // Update hidden id_inventory field
    if (inventoryHidden && inventoryId) {
        inventoryHidden.value = inventoryId;
    }
    
    const unitKerjaId = unitKerjaSelect.value;
    const ruanganId = ruanganSelect ? ruanganSelect.value : '';
    
    // Jika belum pilih inventory item atau unit kerja, kosongkan
    if (!itemValue || !unitKerjaId) {
        nomorRegisterInput.value = '';
        nomorRegisterInput.placeholder = 'Akan di-generate otomatis setelah memilih Inventory Item, Unit Kerja dan Ruangan';
        return;
    }
    
    // Format baru: ID_UNIT_KERJA/ID_RUANGAN/URUT atau ID_UNIT_KERJA/URUT
    let prefix = '';
    if (ruanganId) {
        prefix = `${String(unitKerjaId).padStart(3, '0')}/${String(ruanganId).padStart(3, '0')}`;
    } else {
        prefix = `${String(unitKerjaId).padStart(3, '0')}`;
    }
    
    // Jangan set value, biarkan kosong agar backend yang generate dengan angka yang benar
    // Hanya tampilkan placeholder sebagai preview
    nomorRegisterInput.value = '';
    nomorRegisterInput.placeholder = `Akan di-generate otomatis: ${prefix}/[URUT] (contoh: ${prefix}/0001)`;
}

function filterRuangan(unitKerjaId) {
    const ruanganSelect = document.getElementById('id_ruangan');
    if (!ruanganSelect) return;
    
    const options = ruanganSelect.querySelectorAll('option');
    
    // Tampilkan semua opsi
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block'; // Tampilkan opsi kosong
        } else {
            const unitKerjaOption = option.dataset.unitKerja;
            if (unitKerjaId === '' || unitKerjaOption === unitKerjaId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Reset pilihan jika ruangan yang dipilih tidak sesuai dengan unit kerja
    if (unitKerjaId && ruanganSelect.value) {
        const selectedOption = ruanganSelect.options[ruanganSelect.selectedIndex];
        if (selectedOption.dataset.unitKerja !== unitKerjaId) {
            ruanganSelect.value = '';
        }
    }
    
    // Generate nomor register setelah filter ruangan
    generateNomorRegister();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('id_item');
    const unitKerjaSelect = document.getElementById('id_unit_kerja');
    const ruanganSelect = document.getElementById('id_ruangan');
    
    // Filter ruangan berdasarkan unit kerja yang dipilih
    if (unitKerjaSelect && unitKerjaSelect.value) {
        filterRuangan(unitKerjaSelect.value);
    }
    
    // Generate nomor register saat inventory item berubah
    if (itemSelect) {
        itemSelect.addEventListener('change', generateNomorRegister);
    }
    
    // Generate nomor register saat unit kerja berubah
    if (unitKerjaSelect) {
        unitKerjaSelect.addEventListener('change', function() {
            filterRuangan(this.value);
            generateNomorRegister();
        });
    }
    
    // Generate nomor register saat ruangan berubah
    if (ruanganSelect) {
        ruanganSelect.addEventListener('change', generateNomorRegister);
    }
    
    // Generate awal jika sudah ada nilai
    generateNomorRegister();
});
</script>
@endpush
@endsection
