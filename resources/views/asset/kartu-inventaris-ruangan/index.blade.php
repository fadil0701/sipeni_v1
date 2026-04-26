@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dokumen KIR (Cetak/Download)</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar dokumen KIR per unit kerja untuk kebutuhan cetak dan unduh.</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
            <select 
                id="id_unit_kerja" 
                name="id_unit_kerja" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Semua Unit Kerja</option>
                @foreach($unitOptions as $unit)
                    <option value="{{ $unit->id_unit_kerja }}" {{ (string) request('id_unit_kerja') === (string) $unit->id_unit_kerja ? 'selected' : '' }}>
                        {{ $unit->nama_unit_kerja }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 flex items-end">
            <button 
                type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filter
            </button>
            <a 
                href="{{ route('asset.kartu-inventaris-ruangan.index') }}" 
                class="ml-2 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table
            class="min-w-full divide-y divide-gray-200"
            @if($summaries instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $summaries->firstItem() }}" @endif
        >
            <thead class="bg-gray-50">
                <tr>
                    <x-table.num-th />
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan Unit Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Item KIR</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Update Terakhir</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($summaries as $summary)
                @php
                    $unit = $units[$summary->id_unit_kerja] ?? null;
                    $ruanganNames = $ruanganByUnit[$summary->id_unit_kerja] ?? collect();
                @endphp
                <tr class="hover:bg-gray-50">
                    <x-table.num-td :paginator="$summaries" />
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $unit?->nama_unit_kerja ?? ('Unit #'.$summary->id_unit_kerja) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($ruanganNames->isNotEmpty())
                            {{ $ruanganNames->implode(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                            {{ number_format((int) $summary->total_item, 0, ',', '.') }} item
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ \Illuminate\Support\Carbon::parse($summary->last_update)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a 
                            href="{{ route('asset.kartu-inventaris-ruangan.dokumen-unit', ['id_unit_kerja' => $summary->id_unit_kerja, 'download' => 1]) }}" 
                            class="text-blue-600 hover:text-blue-900 mr-3"
                        >
                            Download Dokumen
                        </a>
                        <a 
                            href="{{ route('asset.kartu-inventaris-ruangan.dokumen-unit', ['id_unit_kerja' => $summary->id_unit_kerja, 'print' => 1]) }}" 
                            target="_blank"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            Cetak
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        Belum ada dokumen KIR yang siap cetak
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($summaries->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $summaries->links() }}
    </div>
    @endif
</div>
@endsection
