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
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Detail Data Inventory</h2>
        <a href="{{ route('inventory.data-inventory.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
        @if($inventory->jenis_inventory === 'ASET')
            <span class="text-sm text-gray-600 px-4 py-2">Untuk edit data inventory, gunakan tombol Edit di halaman daftar. Di halaman ini hanya bisa edit per register.</span>
        @endif
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Barang -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Barang</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Data Barang</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->dataBarang->nama_barang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Jenis Inventory</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $inventory->jenis_inventory == 'ASET' ? 'bg-blue-100 text-blue-800' : ($inventory->jenis_inventory == 'PERSEDIAAN' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ $inventory->jenis_inventory }}
                            </span>
                        </dd>
                    </div>
                    @if($inventory->jenis_barang)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Jenis Barang</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->jenis_barang }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->gudang->nama_gudang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Satuan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->satuan->nama_satuan ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Kuantitas & Harga -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kuantitas & Harga</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Qty Input</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ number_format($inventory->qty_input, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Harga Satuan</dt>
                        <dd class="text-sm font-semibold text-gray-900">Rp {{ number_format($inventory->harga_satuan, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Total Harga</dt>
                        <dd class="text-sm font-semibold text-gray-900">Rp {{ number_format($inventory->total_harga, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tahun Anggaran</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->tahun_anggaran }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $inventory->status_inventory == 'AKTIF' ? 'bg-green-100 text-green-800' : ($inventory->status_inventory == 'DISTRIBUSI' ? 'bg-yellow-100 text-yellow-800' : ($inventory->status_inventory == 'HABIS' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $inventory->status_inventory }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informasi Teknis -->
            @if($inventory->merk || $inventory->tipe || $inventory->spesifikasi)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Teknis</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    @if($inventory->merk)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Merk</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->merk }}</dd>
                    </div>
                    @endif
                    @if($inventory->tipe)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tipe</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->tipe }}</dd>
                    </div>
                    @endif
                    @if($inventory->tahun_produksi)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tahun Produksi</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->tahun_produksi }}</dd>
                    </div>
                    @endif
                    @if($inventory->nama_penyedia)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama Penyedia</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->nama_penyedia }}</dd>
                    </div>
                    @endif
                    @if($inventory->no_seri)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No Seri</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $inventory->no_seri }}</dd>
                    </div>
                    @endif
                    @if($inventory->spesifikasi)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Spesifikasi</dt>
                        <dd class="text-sm text-gray-900">{{ $inventory->spesifikasi }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Foto & Dokumen -->
            @if($inventory->upload_foto || $inventory->upload_dokumen)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Foto & Dokumen</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    @if($inventory->upload_foto)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Foto</dt>
                        <dd class="text-sm text-gray-900">
                            <img src="{{ asset('storage/' . $inventory->upload_foto) }}" alt="Foto Inventory" class="h-48 w-auto rounded-md border border-gray-300 shadow-sm">
                        </dd>
                    </div>
                    @endif
                    @if($inventory->upload_dokumen)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Dokumen</dt>
                        <dd class="text-sm text-gray-900">
                            <a href="{{ asset('storage/' . $inventory->upload_dokumen) }}" target="_blank" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Lihat Dokumen
                            </a>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Register Aset (jika jenis = ASET) -->
            @if($inventory->jenis_inventory == 'ASET')
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Register Aset ({{ $inventory->inventoryItems->count() }} item)</h3>
                @if($inventory->inventoryItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Register</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No Seri</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($inventory->inventoryItems as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->kode_register }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->no_seri ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="space-y-1">
                                        <div class="flex items-center">
                                            <span class="text-xs font-medium text-gray-500 mr-2">Gudang:</span>
                                            <span class="text-xs text-gray-900">{{ $item->gudang->nama_gudang ?? '-' }}</span>
                                        </div>
                                        @if($item->ruangan)
                                        <div class="flex items-center">
                                            <span class="text-xs font-medium text-gray-500 mr-2">Ruangan:</span>
                                            <span class="text-xs text-gray-900">{{ $item->ruangan->nama_ruangan ?? '-' }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->kondisi_item == 'BAIK' ? 'bg-green-100 text-green-800' : ($item->kondisi_item == 'RUSAK_RINGAN' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $item->kondisi_item }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->status_item == 'AKTIF' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->status_item }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium">
                                    <a href="{{ route('inventory.inventory-item.edit', $item->id_item) }}" class="inline-flex items-center px-3 py-1.5 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Perhatian:</strong> Belum ada register aset untuk inventory ini. 
                                @if($inventory->qty_input > 0)
                                    Seharusnya ada {{ number_format($inventory->qty_input, 0, ',', '.') }} register aset berdasarkan qty input. 
                                    Silakan periksa apakah proses auto register aset sudah berjalan dengan benar.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

