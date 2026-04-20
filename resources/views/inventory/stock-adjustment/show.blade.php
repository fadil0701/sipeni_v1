@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('inventory.stock-adjustment.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Stock Adjustment
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Detail Stock Adjustment</h2>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap stock adjustment</p>
            </div>
            <div class="flex space-x-3">
                @php
                    use App\Helpers\PermissionHelper;
                    $user = auth()->user();
                @endphp
                @if($adjustment->status == 'DRAFT' && PermissionHelper::canAccess($user, 'inventory.stock-adjustment.edit'))
                <a 
                    href="{{ route('inventory.stock-adjustment.edit', $adjustment->id_adjustment) }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                @endif
                @if($adjustment->status == 'DRAFT' && PermissionHelper::canAccess($user, 'inventory.stock-adjustment.ajukan'))
                <form action="{{ route('inventory.stock-adjustment.ajukan', $adjustment->id_adjustment) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    >
                        Ajukan
                    </button>
                </form>
                @endif
                @if($adjustment->status == 'DIAJUKAN' && PermissionHelper::canAccess($user, 'inventory.stock-adjustment.approve'))
                <form action="{{ route('inventory.stock-adjustment.approve', $adjustment->id_adjustment) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        Setujui
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Informasi Adjustment -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Adjustment</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Adjustment</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $adjustment->tanggal_adjustment ? $adjustment->tanggal_adjustment->format('d F Y') : '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Adjustment</dt>
                        <dd class="mt-1">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $adjustment->jenis_adjustment }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @php
                                $statusColors = [
                                    'DRAFT' => 'bg-gray-100 text-gray-800',
                                    'DIAJUKAN' => 'bg-yellow-100 text-yellow-800',
                                    'DISETUJUI' => 'bg-green-100 text-green-800',
                                    'DITOLAK' => 'bg-red-100 text-red-800',
                                ];
                                $color = $statusColors[$adjustment->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $color }}">
                                {{ $adjustment->status }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Petugas</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->petugas->name ?? '-' }}
                        </dd>
                    </div>
                    @if($adjustment->approver)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Approver</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->approver->name ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Approval</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->tanggal_approval ? $adjustment->tanggal_approval->format('d F Y H:i') : '-' }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Informasi Stock -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Stock</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $adjustment->dataBarang->nama_barang ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Gudang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->gudang->nama_gudang ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty Sebelum</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ number_format($adjustment->qty_sebelum, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty Sesudah</dt>
                        <dd class="mt-1 text-lg text-blue-600 font-bold">
                            {{ number_format($adjustment->qty_sesudah, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Selisih</dt>
                        <dd class="mt-1">
                            @if($adjustment->qty_selisih > 0)
                                <span class="text-lg text-green-600 font-bold">+{{ number_format($adjustment->qty_selisih, 2) }}</span>
                            @elseif($adjustment->qty_selisih < 0)
                                <span class="text-lg text-red-600 font-bold">{{ number_format($adjustment->qty_selisih, 2) }}</span>
                            @else
                                <span class="text-lg text-gray-500 font-bold">0</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if($adjustment->alasan || $adjustment->keterangan)
            <div class="lg:col-span-2 bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Alasan & Keterangan</h3>
                <dl class="space-y-4">
                    @if($adjustment->alasan)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->alasan }}
                        </dd>
                    </div>
                    @endif
                    @if($adjustment->keterangan)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->keterangan }}
                        </dd>
                    </div>
                    @endif
                    @if($adjustment->catatan_approval)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Catatan Approval</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->catatan_approval }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
