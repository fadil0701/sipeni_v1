@extends('layouts.app')

@section('content')
@php
    use App\Helpers\PermissionHelper;
    $user = auth()->user();
@endphp

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Register Aset (KIB & KIR)</h1>
            <p class="mt-1 text-sm text-gray-600">Ringkasan aset per lokasi. KIB (Gudang Pusat) = semua aset yang belum maupun sudah ter-register. KIR (Gudang Unit) = hanya aset yang sudah ter-register dan ditempatkan di ruangan.</p>
        </div>
        @if(PermissionHelper::canAccess($user, 'asset.register-aset.create'))
        <a 
            href="{{ route('asset.register-aset.create') }}" 
            class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Register Aset
        </a>
        @endif
    </div>
</div>

<!-- Info legend -->
<div class="mb-6 flex flex-wrap items-center gap-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
    <span class="font-medium text-gray-700">Keterangan:</span>
    <span class="inline-flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
        <strong>KIB</strong> — Kartu Inventaris Barang
    </span>
    <span class="inline-flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-full bg-orange-500"></span>
        <strong>KIR</strong> — Kartu Inventaris Ruangan
    </span>
</div>

<!-- Cards Grid -->
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <!-- Card Gudang Pusat (KIB) -->
    @if($gudangPusatData)
    <a href="{{ route('asset.register-aset.unit-kerja.show', ['unit_kerja' => 'pusat']) }}" 
       class="group block bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl hover:border-blue-200 transition-all duration-200">
        <div class="h-1.5 bg-gradient-to-r from-blue-500 to-blue-600"></div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-3 mb-5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $gudangPusatData['nama'] }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Gudang Pusat</p>
                    </div>
                </div>
                <span class="flex-shrink-0 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800">KIB</span>
            </div>
            <div class="grid grid-cols-1 gap-3 mb-4">
                <div class="rounded-lg bg-blue-50 p-3 text-center">
                    <div class="text-xl font-bold text-blue-700">{{ number_format($gudangPusatData['kib_count'], 0, ',', '.') }}</div>
                    <div class="text-xs text-blue-600 mt-0.5">Jumlah Aset Terdaftar</div>
                </div>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 text-blue-600 font-medium text-sm group-hover:text-blue-700">
                <span>Lihat detail aset</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </a>
    @endif
    
    <!-- Card Gudang Unit -->
    @forelse($gudangUnits as $gudang)
    <a href="{{ route('asset.register-aset.unit-kerja.show', ['unit_kerja' => $gudang->id_gudang]) }}" 
       class="group block bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl hover:border-orange-200 transition-all duration-200">
        <div class="h-1.5 bg-gradient-to-r from-orange-500 to-amber-500"></div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-3 mb-5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $gudang->nama_gudang }}</h3>
                        @if($gudang->relationLoaded('unitKerja') && $gudang->unitKerja)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $gudang->unitKerja->nama_unit_kerja }}</p>
                        @else
                        <p class="text-xs text-gray-500 mt-0.5">Gudang Unit</p>
                        @endif
                    </div>
                </div>
                @php $kirCount = $gudang->kir_count ?? 0; @endphp
                <span class="flex-shrink-0 px-2.5 py-1 text-xs font-semibold rounded-lg bg-orange-100 text-orange-800">
                    KIR
                </span>
            </div>
            <div class="grid grid-cols-1 gap-3 mb-4">
                <div class="rounded-lg bg-orange-50 p-3 text-center">
                    <div class="text-xl font-bold text-orange-700">{{ number_format($kirCount, 0, ',', '.') }}</div>
                    <div class="text-xs text-orange-600 mt-0.5">Jumlah Aset Unit Kerja</div>
                </div>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 text-orange-600 font-medium text-sm group-hover:text-orange-700">
                <span>Lihat detail aset</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </a>
    @empty
    @endforelse
</div>

@if($gudangUnits->isEmpty() && (!$gudangPusatData || $gudangPusatData['total_aset'] == 0))
<div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-8 text-center">
    <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
    </div>
    <p class="text-amber-800 font-medium">Belum ada data register aset</p>
    <p class="text-sm text-amber-700 mt-1 max-w-md mx-auto">Pilih "Tambah Register Aset" untuk mendaftarkan aset, atau pastikan sudah ada penerimaan barang ke gudang unit.</p>
</div>
@elseif($gudangUnits->isEmpty() && $gudangPusatData)
<p class="mt-4 text-sm text-gray-500 text-center">Belum ada gudang unit dengan aset. Data di atas hanya menampilkan gudang pusat.</p>
@endif
@endsection
