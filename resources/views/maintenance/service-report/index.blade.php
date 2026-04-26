@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Service Report</h1>
        <p class="text-sm text-gray-600 mt-1">Laporan hasil pengerjaan pemeliharaan aset</p>
    </div>
    <a href="{{ route('maintenance.service-report.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md">Tambah Service Report</a>
</div>
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200" @if($serviceReports instanceof \Illuminate\Contracts\Pagination\Paginator) data-pagination-base="{{ $serviceReports->firstItem() }}" @endif>
        <thead class="bg-gray-50">
            <tr>
                <x-table.num-th />
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No SR</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aset</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($serviceReports as $report)
                <tr>
                    <x-table.num-td :paginator="$serviceReports" />
                    <td class="px-4 py-3 text-sm">{{ $report->no_service_report }}</td>
                    <td class="px-4 py-3 text-sm">{{ $report->registerAset->nomor_register ?? '-' }} - {{ $report->registerAset->inventory->dataBarang->nama_barang ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $report->jenis_service }}</td>
                    <td class="px-4 py-3 text-sm">{{ $report->status_service }}</td>
                    <td class="px-4 py-3 text-sm text-right"><a href="{{ route('maintenance.service-report.show', $report->id_service_report) }}" class="text-blue-600">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada service report.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($serviceReports->hasPages())<div class="p-3 border-t border-gray-100">{{ $serviceReports->links() }}</div>@endif
</div>
@endsection
