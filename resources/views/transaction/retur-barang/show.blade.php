@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.retur-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Retur
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Retur Barang</h2>
            <p class="text-sm text-gray-600 mt-1">No. Retur: <span class="font-semibold">{{ $retur->no_retur }}</span></p>
        </div>
        <div class="flex space-x-3">
            @php
                use App\Helpers\PermissionHelper;
                $user = auth()->user();
            @endphp
            @if(in_array($retur->status_retur, ['DRAFT', 'DIAJUKAN']) && PermissionHelper::canAccess($user, 'transaction.retur-barang.edit'))
                <a 
                    href="{{ route('transaction.retur-barang.edit', $retur->id_retur) }}" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endif
            @if($retur->status_retur == 'DRAFT' && PermissionHelper::canAccess($user, 'transaction.retur-barang.ajukan'))
                <form action="{{ route('transaction.retur-barang.ajukan', $retur->id_retur) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
                    >
                        Ajukan
                    </button>
                </form>
            @endif
            @if($retur->status_retur == 'DIAJUKAN' && PermissionHelper::canAccess($user, 'transaction.retur-barang.terima'))
                <form action="{{ route('transaction.retur-barang.terima', $retur->id_retur) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                        onclick="return confirm('Apakah Anda yakin ingin menerima retur ini? Stock akan diupdate setelah retur diterima.')"
                    >
                        Terima Retur
                    </button>
                </form>
            @endif
            @if($retur->status_retur == 'DIAJUKAN' && PermissionHelper::canAccess($user, 'transaction.retur-barang.tolak'))
                <button 
                    type="button" 
                    onclick="showRejectModal()"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                >
                    Tolak
                </button>
            @endif
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Retur -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Retur</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Retur</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $retur->no_retur }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $statusColor = match($retur->status_retur) {
                                    'DRAFT' => 'bg-gray-100 text-gray-800',
                                    'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                                    'DITERIMA' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $retur->status_retur }}
                            </span>
                        </dd>
                    </div>
                    @if($retur->penerimaan)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Penerimaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('transaction.penerimaan-barang.show', $retur->penerimaan->id_penerimaan) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $retur->penerimaan->no_penerimaan ?? '-' }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($retur->distribusi)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. SBBK</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('transaction.distribusi.show', $retur->distribusi->id_distribusi) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $retur->distribusi->no_sbbk ?? '-' }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $retur->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang Asal</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $retur->gudangAsal->nama_gudang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang Tujuan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $retur->gudangTujuan->nama_gudang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pegawai Pengirim</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $retur->pegawaiPengirim->nama_pegawai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Retur</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $retur->tanggal_retur->format('d/m/Y') }}</dd>
                    </div>
                    @if($retur->alasan_retur)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Alasan Retur</dt>
                        <dd class="text-sm text-gray-900">{{ $retur->alasan_retur }}</dd>
                    </div>
                    @endif
                    @if($retur->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $retur->keterangan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Detail Retur -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Retur ({{ $retur->detailRetur->count() }} item)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Retur</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alasan Retur Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($retur->detailRetur as $index => $detail)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $detail->inventory->dataBarang->nama_barang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $detail->inventory->jenis_barang ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($detail->qty_retur, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->alasan_retur_item ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if($retur->status_retur == 'DIAJUKAN')
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tolak Retur Barang</h3>
            <form action="{{ route('transaction.retur-barang.tolak', $retur->id_retur) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="keterangan_tolak" class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="keterangan_tolak" 
                        name="keterangan" 
                        rows="4"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Alasan penolakan retur..."
                    ></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="hideRejectModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700"
                    >
                        Tolak Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    
    function hideRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endif
@endsection



