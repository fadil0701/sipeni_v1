@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Proses Pengadaan</h1>
        <p class="mt-1 text-sm text-gray-600">Paket pengadaan yang sedang dalam proses (Diajukan / Diproses)</p>
    </div>
    <a href="{{ route('procurement.paket-pengadaan.index') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">Semua Paket Pengadaan</a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('procurement.proses-pengadaan.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="status_paket" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status_paket" name="status_paket" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm min-w-[140px]">
                <option value="">Semua (Diajukan & Diproses)</option>
                <option value="DIAJUKAN" {{ request('status_paket') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                <option value="DIPROSES" {{ request('status_paket') == 'DIPROSES' ? 'selected' : '' }}>Diproses</option>
            </select>
        </div>
        <div>
            <label for="metode_pengadaan" class="block text-sm font-medium text-gray-700 mb-1">Metode</label>
            <select id="metode_pengadaan" name="metode_pengadaan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm min-w-[160px]">
                <option value="">Semua</option>
                <option value="PEMILIHAN_LANGSUNG" {{ request('metode_pengadaan') == 'PEMILIHAN_LANGSUNG' ? 'selected' : '' }}>Pemilihan Langsung</option>
                <option value="PENUNJUKAN_LANGSUNG" {{ request('metode_pengadaan') == 'PENUNJUKAN_LANGSUNG' ? 'selected' : '' }}>Penunjukan Langsung</option>
                <option value="TENDER" {{ request('metode_pengadaan') == 'TENDER' ? 'selected' : '' }}>Tender</option>
                <option value="SWAKELOLA" {{ request('metode_pengadaan') == 'SWAKELOLA' ? 'selected' : '' }}>Swakelola</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Filter</button>
        <a href="{{ route('procurement.proses-pengadaan.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Reset</a>
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
                        @php $sc = ['DIAJUKAN'=>'bg-yellow-100 text-yellow-800','DIPROSES'=>'bg-blue-100 text-blue-800']; $c = $sc[$paket->status_paket] ?? 'bg-gray-100 text-gray-800'; @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c }}">{{ $paket->status_paket }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('procurement.paket-pengadaan.show', $paket->id_paket) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                        <a href="{{ route('procurement.paket-pengadaan.edit', $paket->id_paket) }}" class="ml-3 text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">Tidak ada paket dalam proses.</td>
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
