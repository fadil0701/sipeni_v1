@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Paket Pengadaan</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar paket pengadaan barang dan jasa</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('procurement.proses-pengadaan.index') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Proses Pengadaan
        </a>
        <a href="{{ route('procurement.paket-pengadaan.create') }}" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Paket
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded"><p class="text-sm font-medium text-green-800">{{ session('success') }}</p></div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded"><p class="text-sm font-medium text-red-800">{{ session('error') }}</p></div>
@endif

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('procurement.paket-pengadaan.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label for="status_paket" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status_paket" name="status_paket" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua</option>
                <option value="DRAFT" {{ request('status_paket') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="DIAJUKAN" {{ request('status_paket') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                <option value="DIPROSES" {{ request('status_paket') == 'DIPROSES' ? 'selected' : '' }}>Diproses</option>
                <option value="SELESAI" {{ request('status_paket') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                <option value="DIBATALKAN" {{ request('status_paket') == 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div>
            <label for="metode_pengadaan" class="block text-sm font-medium text-gray-700 mb-1">Metode</label>
            <select id="metode_pengadaan" name="metode_pengadaan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua</option>
                <option value="PEMILIHAN_LANGSUNG" {{ request('metode_pengadaan') == 'PEMILIHAN_LANGSUNG' ? 'selected' : '' }}>Pemilihan Langsung</option>
                <option value="PENUNJUKAN_LANGSUNG" {{ request('metode_pengadaan') == 'PENUNJUKAN_LANGSUNG' ? 'selected' : '' }}>Penunjukan Langsung</option>
                <option value="TENDER" {{ request('metode_pengadaan') == 'TENDER' ? 'selected' : '' }}>Tender</option>
                <option value="SWAKELOLA" {{ request('metode_pengadaan') == 'SWAKELOLA' ? 'selected' : '' }}>Swakelola</option>
            </select>
        </div>
        <div>
            <label for="id_sub_kegiatan" class="block text-sm font-medium text-gray-700 mb-1">Sub Kegiatan</label>
            <select id="id_sub_kegiatan" name="id_sub_kegiatan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua</option>
                @foreach($subKegiatanList ?? [] as $sk)
                    <option value="{{ $sk->id_sub_kegiatan }}" {{ request('id_sub_kegiatan') == $sk->id_sub_kegiatan ? 'selected' : '' }}>{{ $sk->nama_sub_kegiatan }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Filter</button>
            <a href="{{ route('procurement.paket-pengadaan.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pakets as $paket)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $paket->no_paket }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $paket->nama_paket }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $paket->subKegiatan?->nama_sub_kegiatan ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $paket->metode_pengadaan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">Rp {{ number_format($paket->nilai_paket ?? 0, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $sc = ['DRAFT'=>'bg-gray-100 text-gray-800','DIAJUKAN'=>'bg-yellow-100 text-yellow-800','DIPROSES'=>'bg-blue-100 text-blue-800','SELESAI'=>'bg-green-100 text-green-800','DIBATALKAN'=>'bg-red-100 text-red-800'];
                            $c = $sc[$paket->status_paket] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c }}">{{ $paket->status_paket }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('procurement.paket-pengadaan.show', $paket->id_paket) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                        <a href="{{ route('procurement.paket-pengadaan.edit', $paket->id_paket) }}" class="ml-3 text-indigo-600 hover:text-indigo-900">Edit</a>
                        @if(!$paket->kontrak)
                        <form action="{{ route('procurement.paket-pengadaan.destroy', $paket->id_paket) }}" method="POST" class="inline ml-1" onsubmit="return confirm('Yakin hapus paket ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">Belum ada paket pengadaan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pakets->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">{{ $pakets->links() }}</div>
    @endif
</div>
@endsection
