@extends('layouts.app')

@section('content')
    @include('inventory.partials.stock-merk-breakdown-content', [
        'pageTitle' => 'Rincian stok per merk (Kartu Stok)',
        'pageSubtitle' => 'Kartu stok menampilkan satu baris agregat; halaman ini memecah quantity per merk dan per baris inventory.',
        'backUrl' => $backUrl,
        'backLabel' => 'Kembali ke Kartu Stok',
        'stock' => $stock,
        'breakdownRows' => $breakdownRows,
        'sumInventory' => $sumInventory,
    ])
@endsection
