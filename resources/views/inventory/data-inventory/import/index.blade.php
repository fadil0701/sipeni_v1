@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Import Data Inventory</h1>
    <p class="mt-1 text-sm text-gray-600">Import massal `data_inventory` dari file Excel. Untuk `ASET`, sistem otomatis membuat `inventory_item`. Untuk `PERSEDIAAN/FARMASI`, sistem meng-update `data_stock`.</p>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <ul class="list-disc pl-5 text-sm text-red-800 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventory.data-inventory.import.template') }}" class="inline-flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
            Download Template Excel
        </a>
    </div>

    <div class="rounded-md border border-blue-100 bg-blue-50 p-4">
        <h2 class="text-sm font-semibold text-blue-900 mb-2">Format wajib</h2>
        <ul class="list-disc pl-5 text-sm text-blue-800 space-y-1">
            <li>Gunakan sheet `data_inventory` pada template resmi.</li>
            <li>Header kolom tidak boleh diubah.</li>
            <li>Tanggal kedaluwarsa disarankan format `YYYY-MM-DD`.</li>
            <li>Untuk `FARMASI`, kolom `no_batch` dan `tanggal_kedaluwarsa` wajib terisi.</li>
            <li>Kolom `upload_foto` dan `upload_dokumen` belum diproses oleh importer.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('inventory.data-inventory.import.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
            <div class="space-y-2">
                <input
                    type="file"
                    id="file"
                    name="file"
                    accept=".xlsx,.xls"
                    required
                    class="sr-only"
                >
                <label for="file" class="inline-flex cursor-pointer items-center rounded-md bg-gray-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-gray-700">
                    Choose File
                </label>
                <span id="import-inventory-file-name" class="text-sm text-gray-600">Belum ada file dipilih</span>
            </div>
        </div>

        <div>
            <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                Import Data
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('file');
        const fileNameLabel = document.getElementById('import-inventory-file-name');
        if (!fileInput || !fileNameLabel) return;

        fileInput.addEventListener('change', function () {
            const fileName = fileInput.files && fileInput.files.length ? fileInput.files[0].name : '';
            fileNameLabel.textContent = fileName || 'Belum ada file dipilih';
        });
    });
</script>
@endpush

