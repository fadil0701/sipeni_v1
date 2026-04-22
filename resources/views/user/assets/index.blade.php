@extends('layouts.app')

@section('content')
<div class="w-full py-2">
    <div class="mb-5">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Aset Saya</h1>
            <p class="mt-1 text-sm text-gray-600">Daftar aset unit yang dapat Anda lihat berdasarkan hak akses.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table
                class="min-w-full divide-y divide-gray-200"
                @if($assets instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $assets->firstItem() }}" @endif
            >
                <thead class="bg-gray-50/80">
                    <tr>
                        <x-table.num-th />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <x-table.num-td :paginator="$assets" />
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $asset->inventory->dataBarang->nama_barang ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $asset->kode_register ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $asset->ruangan->nama_ruangan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $status = $asset->kondisi_item ?? 'N/A';
                                    $color = match($status) {
                                        'BAIK' => 'bg-green-100 text-green-800',
                                        'RUSAK_RINGAN' => 'bg-yellow-100 text-yellow-800',
                                        'RUSAK_BERAT' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                    {{ $status === 'BAIK' ? 'Baik' : $status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('user.assets.show', $asset->id_item) }}" class="text-blue-600 hover:text-blue-900 underline-offset-2 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                Belum ada data aset untuk ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection

