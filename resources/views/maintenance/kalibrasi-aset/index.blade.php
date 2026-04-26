@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kalibrasi Aset</h1>
        <p class="text-sm text-gray-600 mt-1">Monitoring jadwal dan hasil kalibrasi aset</p>
    </div>
    <a href="{{ route('maintenance.kalibrasi-aset.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md">Tambah Kalibrasi</a>
</div>
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200" @if($kalibrasis instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $kalibrasis->firstItem() }}" @endif>
        <thead class="bg-gray-50">
            <tr>
                <x-table.num-th />
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No Kalibrasi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aset</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Kalibrasi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kadaluarsa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($kalibrasis as $kalibrasi)
                <tr>
                    <x-table.num-td :paginator="$kalibrasis" />
                    <td class="px-4 py-3 text-sm">{{ $kalibrasi->no_kalibrasi }}</td>
                    <td class="px-4 py-3 text-sm">{{ $kalibrasi->registerAset->nomor_register ?? '-' }} - {{ $kalibrasi->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ optional($kalibrasi->tanggal_kalibrasi)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm">{{ optional($kalibrasi->tanggal_kadaluarsa)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $kalibrasi->status_kalibrasi }}</td>
                    <td class="px-4 py-3 text-sm text-right"><a href="{{ route('maintenance.kalibrasi-aset.show', $kalibrasi->id_kalibrasi) }}" class="text-blue-600">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada data kalibrasi.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($kalibrasis->hasPages())<div class="p-3 border-t border-gray-100">{{ $kalibrasis->links() }}</div>@endif
</div>
@endsection
