@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Rekap Perencanaan Tahunan</h1>
        <p class="mt-1 text-sm text-gray-600">Rekap RKU per Program, Kegiatan, dan Sub Kegiatan per tahun anggaran</p>
    </div>
    <a 
        href="{{ route('planning.rku.index') }}" 
        class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Status Perencanaan
    </a>
</div>

<!-- Filter Tahun -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('planning.rekap-tahunan') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun Anggaran</label>
            <select 
                id="tahun" 
                name="tahun" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm min-w-[120px]"
            >
                @foreach($tahunList ?? [] as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button 
            type="submit" 
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Tampilkan
        </button>
    </form>
</div>

<!-- Rekap Table -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900">Tahun Anggaran {{ $tahun }}</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Kegiatan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah RKU</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Anggaran</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php $grandTotalRku = 0; $grandTotalAnggaran = 0; @endphp
                @forelse($rekap ?? [] as $idProgram => $programData)
                    @php $programRowspan = 0; @endphp
                    @foreach($programData['kegiatan'] ?? [] as $kegiatanData)
                        @foreach($kegiatanData['sub_kegiatan'] ?? [] as $subData)
                            @php $programRowspan++; @endphp
                        @endforeach
                    @endforeach
                    @php $programFirst = true; @endphp
                    @foreach($programData['kegiatan'] ?? [] as $idKegiatan => $kegiatanData)
                        @php $kegiatanRowspan = count($kegiatanData['sub_kegiatan'] ?? []); $kegiatanFirst = true; @endphp
                        @foreach($kegiatanData['sub_kegiatan'] ?? [] as $subData)
                            <tr class="hover:bg-gray-50">
                                @if($programFirst)
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium align-top border-r border-gray-100" rowspan="{{ $programRowspan }}">
                                        {{ $programData['nama_program'] }}
                                    </td>
                                    @php $programFirst = false; @endphp
                                @endif
                                @if($kegiatanFirst)
                                    <td class="px-6 py-4 text-sm text-gray-700 align-top border-r border-gray-100" rowspan="{{ $kegiatanRowspan }}">
                                        {{ $kegiatanData['nama_kegiatan'] }}
                                    </td>
                                    @php $kegiatanFirst = false; @endphp
                                @endif
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $subData['nama_sub_kegiatan'] }}
                                    @if($subData['kode_sub_kegiatan'])
                                        <span class="text-gray-500">({{ $subData['kode_sub_kegiatan'] }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($subData['jumlah_rku'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                                    Rp {{ number_format($subData['total_anggaran'], 0, ',', '.') }}
                                </td>
                                @php 
                                    $grandTotalRku += $subData['jumlah_rku']; 
                                    $grandTotalAnggaran += $subData['total_anggaran']; 
                                @endphp
                            </tr>
                        @endforeach
                    @endforeach
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data rekap</h3>
                        <p class="mt-1 text-sm text-gray-500">Tidak ada RKU untuk tahun anggaran {{ $tahun }}.</p>
                    </td>
                </tr>
                @endforelse
                @if(!empty($rekap))
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="3" class="px-6 py-3 text-sm text-gray-900">Total</td>
                    <td class="px-6 py-3 text-sm text-right text-gray-900">{{ number_format($grandTotalRku, 0, ',', '.') }}</td>
                    <td class="px-6 py-3 text-sm text-right text-gray-900">Rp {{ number_format($grandTotalAnggaran, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
