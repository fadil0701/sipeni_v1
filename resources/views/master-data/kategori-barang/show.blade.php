@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-data.kategori-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Kategori Barang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Detail Kategori Barang</h2>
        <a href="{{ route('master-data.kategori-barang.edit', $kategoriBarang->id_kategori_barang) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </a>
    </div>
    
    <div class="p-6">
        <dl class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Kode Kategori Barang</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $kategoriBarang->kode_kategori_barang }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Nama Kategori Barang</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $kategoriBarang->nama_kategori_barang }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Kode Barang</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $kategoriBarang->kodeBarang->kode_barang ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Nama Kode Barang</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $kategoriBarang->kodeBarang->nama_kode_barang ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection

