@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.draft-distribusi.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Disposisi
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Detail Draft Distribusi - {{ $kategoriGudang }}</h2>
        <p class="mt-1 text-sm text-gray-600">No. Permintaan: {{ $approvalLog->permintaan->no_permintaan }}</p>
    </div>
    
    <div class="p-6 space-y-6">
        <!-- Informasi Permintaan -->
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Permintaan</h3>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $approvalLog->permintaan->unitKerja->nama_unit_kerja ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pemohon</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $approvalLog->permintaan->pemohon->nama_pegawai ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Permintaan</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $approvalLog->permintaan->tanggal_permintaan->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status Approval</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full 
                            {{ $approvalLog->status == 'DIPROSES' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $approvalLog->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Draft Items -->
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Draft Distribusi - {{ $kategoriGudang }}</h3>
            @if($draftDetails->count() > 0)
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis Barang</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Gudang Asal</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Distribusi</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($draftDetails as $draft)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $draft->inventory->dataBarang->nama_barang ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $draft->inventory->jenis_barang ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $draft->gudangAsal->nama_gudang ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($draft->qty_distribusi, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $draft->satuan->nama_satuan ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">Rp {{ number_format($draft->harga_satuan, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 font-medium">Rp {{ number_format($draft->subtotal, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $draft->status == 'READY' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $draft->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="5" class="px-4 py-2 text-sm text-gray-900 text-right">Total:</td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    Rp {{ number_format($draftDetails->sum('subtotal'), 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="text-sm text-yellow-800">Belum ada draft detail distribusi untuk kategori {{ $kategoriGudang }}.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

