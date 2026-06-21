@php
    $helpText = $helpText ?? 'Ringkasan mengikuti filter di atas. Klik kartu untuk membuka tabel rinci hanya untuk gudang tersebut.';
    $emptyMessage = $emptyMessage ?? 'Tidak ada gudang yang dapat ditampilkan.';
@endphp
@if(($tampilan ?? 'tabel') === 'cards')
    <div class="mb-6">
        <p class="text-sm text-gray-600 mb-4">{{ $helpText }}</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($gudangCards ?? [] as $card)
                @php
                    $g = $card['gudang'];
                    $href = route($routeName, array_merge(
                        request()->except('page'),
                        ['gudang' => $g->id_gudang, 'tampilan' => 'tabel']
                    ));
                @endphp
                <a
                    href="{{ $href }}"
                    class="block rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-gray-900 truncate">{{ $g->nama_gudang }}</h2>
                            @if(!empty($g->kategori_gudang))
                                <p class="mt-0.5 text-xs text-gray-500">{{ $g->kategori_gudang }}</p>
                            @endif
                        </div>
                        @if(!empty($g->jenis_gudang))
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $g->jenis_gudang === 'PUSAT' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $g->jenis_gudang === 'PUSAT' ? 'Pusat' : 'Unit' }}
                            </span>
                        @endif
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Baris stok</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format($card['sku_count'], 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Total qty</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format($card['qty_sum'], 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-xs font-medium text-blue-600">Buka tabel →</p>
                </a>
            @endforeach
        </div>
        @if(($gudangCards ?? collect())->isEmpty())
            <p class="mt-4 text-sm text-gray-500">{{ $emptyMessage }}</p>
        @endif
    </div>
@endif
