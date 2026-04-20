@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Data Inventory</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar semua data inventory barang dan aset</p>
    </div>
    @php
        use App\Helpers\PermissionHelper;
        $user = auth()->user();
    @endphp
    @if(PermissionHelper::canAccess($user, 'inventory.data-inventory.create'))
    <a 
        href="{{ route('inventory.data-inventory.create') }}" 
        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Inventory
    </a>
    @endif
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('inventory.data-inventory.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <div>
            <label for="jenis_inventory" class="block text-sm font-medium text-gray-700 mb-1">Jenis Inventory</label>
            <select 
                id="jenis_inventory" 
                name="jenis_inventory" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Jenis</option>
                <option value="ASET" {{ request('jenis_inventory') == 'ASET' ? 'selected' : '' }}>Aset</option>
                <option value="PERSEDIAAN" {{ request('jenis_inventory') == 'PERSEDIAAN' ? 'selected' : '' }}>Persediaan</option>
                <option value="FARMASI" {{ request('jenis_inventory') == 'FARMASI' ? 'selected' : '' }}>Farmasi</option>
            </select>
        </div>

        <div>
            <label for="gudang" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
            <select 
                id="gudang" 
                name="gudang" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Gudang</option>
                @foreach($gudangs as $gudang)
                    <option value="{{ $gudang->id_gudang }}" {{ request('gudang') == $gudang->id_gudang ? 'selected' : '' }}>
                        {{ $gudang->nama_gudang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="merk" class="block text-sm font-medium text-gray-700 mb-1">Merk</label>
            <input 
                type="text" 
                id="merk" 
                name="merk" 
                value="{{ request('merk') }}"
                placeholder="Cari merk..."
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>

        <div>
            <label for="jenis_barang" class="block text-sm font-medium text-gray-700 mb-1">Jenis Barang</label>
            <select 
                id="jenis_barang" 
                name="jenis_barang" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Jenis Barang</option>
                <optgroup label="Aset">
                    <option value="ALKES" {{ request('jenis_barang') == 'ALKES' ? 'selected' : '' }}>ALKES</option>
                    <option value="NON ALKES" {{ request('jenis_barang') == 'NON ALKES' ? 'selected' : '' }}>NON ALKES</option>
                </optgroup>
                <optgroup label="Farmasi">
                    <option value="OBAT" {{ request('jenis_barang') == 'OBAT' ? 'selected' : '' }}>OBAT</option>
                    <option value="Vaksin" {{ request('jenis_barang') == 'Vaksin' ? 'selected' : '' }}>Vaksin</option>
                    <option value="BHP" {{ request('jenis_barang') == 'BHP' ? 'selected' : '' }}>BHP</option>
                    <option value="BMHP" {{ request('jenis_barang') == 'BMHP' ? 'selected' : '' }}>BMHP</option>
                    <option value="REAGEN" {{ request('jenis_barang') == 'REAGEN' ? 'selected' : '' }}>REAGEN</option>
                    <option value="ALKES" {{ request('jenis_barang') == 'ALKES' ? 'selected' : '' }}>ALKES</option>
                </optgroup>
                <optgroup label="Persediaan">
                    <option value="ATK" {{ request('jenis_barang') == 'ATK' ? 'selected' : '' }}>ATK</option>
                    <option value="ART" {{ request('jenis_barang') == 'ART' ? 'selected' : '' }}>ART</option>
                    <option value="CETAKAN UMUM" {{ request('jenis_barang') == 'CETAKAN UMUM' ? 'selected' : '' }}>CETAKAN UMUM</option>
                    <option value="CETAK KHUSUS" {{ request('jenis_barang') == 'CETAK KHUSUS' ? 'selected' : '' }}>CETAK KHUSUS</option>
                </optgroup>
            </select>
        </div>

        <div>
            <label for="no_batch" class="block text-sm font-medium text-gray-700 mb-1">Nomor Batch</label>
            <input 
                type="text" 
                id="no_batch" 
                name="no_batch" 
                value="{{ request('no_batch') }}"
                placeholder="Cari nomor batch..."
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>

        <div class="sm:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari nama barang atau kode..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
        </div>
    </form>
</div>

<!-- Success Message -->
@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

<!-- Table Card -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Batch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventories as $inventory)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $inventory->dataBarang->kode_data_barang ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $inventory->dataBarang->nama_barang ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $jenisColor = match($inventory->jenis_inventory) {
                                    'ASET' => 'bg-blue-100 text-blue-800',
                                    'PERSEDIAAN' => 'bg-green-100 text-green-800',
                                    'FARMASI' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $jenisColor }}">
                                {{ $inventory->jenis_inventory }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inventory->jenis_barang ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inventory->merk ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inventory->no_batch ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($inventory->qty_input, 0, ',', '.') }} {{ $inventory->satuan->nama_satuan ?? '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($inventory->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inventory->gudang->nama_gudang ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($inventory->status_inventory) {
                                    'AKTIF' => 'bg-green-100 text-green-800',
                                    'DISTRIBUSI' => 'bg-yellow-100 text-yellow-800',
                                    'HABIS' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $inventory->status_inventory }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a 
                                    href="{{ route('inventory.data-inventory.show', $inventory->id_inventory) }}" 
                                    class="text-blue-600 hover:text-blue-900 transition-colors"
                                >
                                    Detail
                                </a>
                                <a 
                                    href="{{ route('inventory.data-inventory.edit', $inventory->id_inventory) }}" 
                                    class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                >
                                    Edit
                                </a>
                                @if(PermissionHelper::canAccess($user, 'inventory.data-inventory.destroy'))
                                <form 
                                    action="{{ route('inventory.data-inventory.destroy', $inventory->id_inventory) }}" 
                                    method="POST" 
                                    class="inline" 
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="text-red-600 hover:text-red-900 transition-colors"
                                    >
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan data inventory baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($inventories->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $inventories->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto submit form on filter change for selects
    document.querySelectorAll('#jenis_inventory, #gudang, #jenis_barang').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    // Submit form on Enter key for text inputs
    document.querySelectorAll('#merk, #no_batch, #search').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    });
</script>
@endpush
@endsection

