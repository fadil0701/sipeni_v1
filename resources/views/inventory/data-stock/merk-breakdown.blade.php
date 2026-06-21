@extends('layouts.app')

@section('content')
    @include('inventory.partials.stock-merk-breakdown-content', [
        'pageTitle' => 'Rincian stok per merk',
        'pageSubtitle' => 'Pecahan quantity berdasarkan merk untuk satu barang pada satu gudang.',
        'backUrl' => $backUrl,
        'backLabel' => 'Kembali ke Data Stok Gudang',
        'stock' => $stock,
        'breakdownRows' => $breakdownRows,
        'sumInventory' => $sumInventory,
    ])
@endsection
