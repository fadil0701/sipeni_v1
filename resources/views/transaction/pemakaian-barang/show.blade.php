@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.pemakaian-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Pemakaian
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
    </div>
@endif

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Pemakaian Barang</h2>
            <p class="text-sm text-gray-600 mt-1">No. Pemakaian: <span class="font-semibold">{{ $pemakaian->no_pemakaian }}</span></p>
        </div>
        <div class="flex space-x-3">
            @php
                use App\Helpers\PermissionHelper;
                $user = auth()->user();
            @endphp
            @if(in_array($pemakaian->status_pemakaian, ['DRAFT']) && PermissionHelper::canAccess($user, 'transaction.pemakaian-barang.edit'))
                <a 
                    href="{{ route('transaction.pemakaian-barang.edit', $pemakaian->id_pemakaian) }}" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endif
            @if($pemakaian->status_pemakaian == 'DRAFT' && PermissionHelper::canAccess($user, 'transaction.pemakaian-barang.ajukan'))
                <form action="{{ route('transaction.pemakaian-barang.ajukan', $pemakaian->id_pemakaian) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
                        onclick="return confirm('Apakah Anda yakin ingin mengajukan pemakaian ini untuk persetujuan?')"
                    >
                        Ajukan
                    </button>
                </form>
            @endif
            @if($pemakaian->status_pemakaian == 'DIAJUKAN' && PermissionHelper::canAccess($user, 'transaction.pemakaian-barang.approve'))
                <form action="{{ route('transaction.pemakaian-barang.approve', $pemakaian->id_pemakaian) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                        onclick="return confirm('Apakah Anda yakin ingin menyetujui pemakaian ini? Stock akan diupdate setelah disetujui.')"
                    >
                        Setujui
                    </button>
                </form>
            @endif
            @if($pemakaian->status_pemakaian == 'DIAJUKAN' && PermissionHelper::canAccess($user, 'transaction.pemakaian-barang.reject'))
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
            <!-- Informasi Pemakaian -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pemakaian</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Pemakaian</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->no_pemakaian }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $statusColor = match($pemakaian->status_pemakaian) {
                                    'DRAFT' => 'bg-gray-100 text-gray-800',
                                    'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                                    'DISETUJUI' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $pemakaian->status_pemakaian }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Gudang</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->gudang->nama_gudang ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pegawai Pemakai</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->pegawaiPemakai->nama_pegawai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Pemakaian</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->tanggal_pemakaian->format('d/m/Y') }}</dd>
                    </div>
                    @if($pemakaian->alasan_pemakaian)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Alasan Pemakaian</dt>
                        <dd class="text-sm text-gray-900">{{ $pemakaian->alasan_pemakaian }}</dd>
                    </div>
                    @endif
                    @if($pemakaian->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $pemakaian->keterangan }}</dd>
                    </div>
                    @endif
                    @if($pemakaian->approver)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Disetujui Oleh</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->approver->name ?? '-' }}</dd>
                    </div>
                    @endif
                    @if($pemakaian->tanggal_approval)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Approval</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pemakaian->tanggal_approval->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                    @if($pemakaian->catatan_approval)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Catatan Approval</dt>
                        <dd class="text-sm text-gray-900">{{ $pemakaian->catatan_approval }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Detail Pemakaian -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Pemakaian</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Pemakaian</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan Pemakaian Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pemakaian->detailPemakaian as $index => $detail)
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $detail->inventory->dataBarang->nama_barang ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $detail->inventory->jenis_barang ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($detail->qty_pemakaian, 2) }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-900">{{ $detail->alasan_pemakaian_item ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-900">{{ $detail->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">Tidak ada detail pemakaian</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tolak Pemakaian Barang</h3>
            <form action="{{ route('transaction.pemakaian-barang.reject', $pemakaian->id_pemakaian) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="catatan_approval" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="catatan_approval" 
                        name="catatan_approval" 
                        rows="4"
                        required
                        placeholder="Masukkan alasan penolakan"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endpush
@endsection

