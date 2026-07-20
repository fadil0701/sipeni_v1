@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.penerimaan-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Penerimaan
    </a>
</div>

@if($penerimaan->status_penerimaan === 'DITERIMA')
<div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
    Penerimaan ini sudah disahkan (sesuai pengiriman). Data tidak dapat diubah.
</div>
@endif

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Penerimaan Barang</h2>
            <p class="text-sm text-gray-600 mt-1">No. Penerimaan: <span class="font-semibold">{{ $penerimaan->no_penerimaan }}</span></p>
        </div>
        <div class="flex space-x-3">
            @php
                $user = auth()->user();
            @endphp
            @if($penerimaan->status_penerimaan === 'DITOLAK' && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.edit'))
            <a 
                href="{{ route('transaction.penerimaan-barang.edit', $penerimaan->id_penerimaan) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                title="Koreksi data setelah verifikasi tidak sesuai"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Koreksi
            </a>
            @endif
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Penerimaan</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Penerimaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->no_penerimaan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @php
                                $statusColor = \App\Support\UiColor::badgeForStatus($penerimaan->status_penerimaan);
                                $statusLabel = match($penerimaan->status_penerimaan) {
                                    'MENUNGGU_BUKTI_SAMPAI' => 'Menunggu bukti sampai',
                                    'MENUNGGU_VERIFIKASI' => 'Menunggu verifikasi',
                                    default => $penerimaan->status_penerimaan,
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. SBBK</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('transaction.distribusi.show', $penerimaan->distribusi->id_distribusi) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $penerimaan->distribusi->no_sbbk ?? '-' }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pegawai Penerima</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->pegawaiPenerima->nama_pegawai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Penerimaan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $penerimaan->tanggal_penerimaan->format('d/m/Y') }}</dd>
                    </div>
                    @if($penerimaan->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $penerimaan->keterangan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Detail Penerimaan -->
            @php
                $hasFarmasiPersediaan = $penerimaan->detailPenerimaan->filter(function ($detail) {
                    $kategoriGudang = $detail->inventory->gudang->kategori_gudang ?? null;
                    return in_array($kategoriGudang, ['FARMASI', 'PERSEDIAAN']);
                })->count() > 0;
                $hasAset = $penerimaan->detailPenerimaan->filter(function ($detail) {
                    $kategoriGudang = $detail->inventory->gudang->kategori_gudang ?? null;
                    return $kategoriGudang === 'ASET';
                })->count() > 0;
                $canVerify = $penerimaan->status_penerimaan === 'MENUNGGU_VERIFIKASI'
                    && $penerimaan->hasBuktiSampai()
                    && (
                        \App\Helpers\PermissionHelper::canAccess(auth()->user(), 'transaction.penerimaan-barang.update')
                        || \App\Helpers\PermissionHelper::canAccess(auth()->user(), 'transaction.penerimaan-barang.store')
                    );
            @endphp
            <div>
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detail Penerimaan ({{ $penerimaan->detailPenerimaan->count() }} item)</h3>
                    @if($canVerify)
                        <button
                            type="button"
                            id="btn_sesuai_semua"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md border border-green-300 text-green-900 bg-green-50 hover:bg-green-100"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Sesuai semua
                        </button>
                    @endif
                </div>

                @if($canVerify)
                <form method="POST" action="{{ route('transaction.penerimaan-barang.verify', $penerimaan) }}" id="formVerifikasiDetail" class="space-y-4">
                    @csrf
                @endif

                <div class="-mx-1 sm:mx-0">
                    <p class="mb-2 px-1 text-xs text-gray-500 sm:hidden">Geser tabel ke samping untuk melihat Verifikasi &amp; Keterangan.</p>
                    <div class="overflow-x-auto overscroll-x-contain rounded-md border border-gray-100" style="-webkit-overflow-scrolling: touch">
                    <table class="w-full table-fixed divide-y divide-gray-200 text-sm" id="tabelDetailPenerimaan">
                        <colgroup>
                            <col style="width:2.5rem">
                            <col>
                            <col style="width:4.5rem">
                            <col style="width:5rem">
                            <col style="width:5rem">
                            <col style="width:4rem">
                            @if($hasFarmasiPersediaan)
                            <col style="width:6.5rem">
                            <col style="width:5.5rem">
                            @endif
                            @if($hasAset)
                            <col style="width:7rem">
                            @endif
                            @if($canVerify)
                            <col style="width:4.75rem">
                            <col style="width:30%">
                            @else
                            <col style="width:30%">
                            @endif
                        </colgroup>
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty Kirim</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty Terima</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                @if($hasFarmasiPersediaan)
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Exp</th>
                                @endif
                                @if($hasAset)
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Seri</th>
                                @endif
                                @if($canVerify)
                                <th class="px-1 py-2 text-center text-xs font-medium text-gray-500 uppercase">Verifikasi</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                @else
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($penerimaan->detailPenerimaan as $index => $detail)
                            @php
                                $detailDistribusi = $penerimaan->distribusi->detailDistribusi->firstWhere('id_inventory', $detail->id_inventory);
                                $qtyDikirim = $detailDistribusi ? $detailDistribusi->qty_distribusi : 0;
                                $qtyDiterima = $detail->qty_diterima ?? 0;
                                $inventory = $detail->inventory;
                                $kategoriGudang = $inventory->gudang->kategori_gudang ?? null;
                                $isAset = $kategoriGudang === 'ASET';
                                $isFarmasiPersediaan = in_array($kategoriGudang, ['FARMASI', 'PERSEDIAAN']);
                                $noSeriList = collect();
                                if ($isAset) {
                                    $inventoryItems = \App\Models\InventoryItem::where('id_inventory', $inventory->id_inventory)
                                        ->where('id_gudang', $inventory->id_gudang)
                                        ->where('status_item', 'AKTIF')
                                        ->limit((int) $qtyDiterima)
                                        ->get();
                                    $noSeriList = $inventoryItems->pluck('no_seri')->filter()->unique()->values();
                                }
                                $oldHasil = old("items.$index.hasil", $detail->hasil_verifikasi);
                                $oldKet = old("items.$index.keterangan", $detail->keterangan);
                            @endphp
                            <tr class="js-verifikasi-row" data-index="{{ $index }}">
                                <td class="px-2 py-2 text-center text-gray-600 tabular-nums text-xs align-middle">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 align-middle">
                                    <span class="block break-words leading-snug">{{ $inventory->dataBarang->nama_barang ?? '-' }}</span>
                                </td>
                                <td class="px-2 py-2 text-gray-900 align-middle">
                                    <span class="block break-words leading-snug">{{ $inventory->jenis_barang ?? '-' }}</span>
                                </td>
                                <td class="px-2 py-2 text-right tabular-nums whitespace-nowrap text-gray-900 align-middle">{{ number_format($qtyDikirim, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-right tabular-nums whitespace-nowrap text-gray-900 align-middle">{{ number_format($qtyDiterima, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-gray-900 align-middle">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                @if($hasFarmasiPersediaan)
                                <td class="px-2 py-2 text-gray-600 align-middle">
                                    @if($isFarmasiPersediaan)
                                        <span class="block break-words">{{ $inventory->no_batch ?? '-' }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-gray-600 align-middle">
                                    @if($isFarmasiPersediaan && $inventory->tanggal_kedaluwarsa)
                                        {{ \Carbon\Carbon::parse($inventory->tanggal_kedaluwarsa)->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                @endif
                                @if($hasAset)
                                <td class="px-2 py-2 text-gray-900 align-middle">
                                    @if($isAset)
                                        @if($noSeriList->count() > 0)
                                            <span class="block break-words">{{ $noSeriList->join(', ') }}</span>
                                        @else
                                            <span class="block break-words">{{ $inventory->no_seri ?? '-' }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                @endif

                                @if($canVerify)
                                <td class="w-0 whitespace-nowrap px-1 py-2 align-middle text-center">
                                    <input type="hidden" name="items[{{ $index }}][id_detail_penerimaan]" value="{{ $detail->id_detail_penerimaan }}">
                                    <input type="hidden" name="items[{{ $index }}][hasil]" class="js-hasil-input" value="{{ $oldHasil }}" required>
                                    <div class="inline-flex items-center justify-center gap-0.5">
                                        <button
                                            type="button"
                                            class="js-btn-sesuai inline-flex h-7 w-7 shrink-0 items-center justify-center rounded border {{ $oldHasil === 'sesuai' ? 'border-green-600 bg-green-600 text-white' : 'border-green-300 bg-white text-green-700 hover:bg-green-50' }}"
                                            data-value="sesuai"
                                            title="Sesuai"
                                            aria-label="Sesuai"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="js-btn-tidak inline-flex h-7 w-7 shrink-0 items-center justify-center rounded border {{ $oldHasil === 'tidak_sesuai' ? 'border-red-600 bg-red-600 text-white' : 'border-red-300 bg-white text-red-700 hover:bg-red-50' }}"
                                            data-value="tidak_sesuai"
                                            title="Tidak sesuai"
                                            aria-label="Tidak sesuai"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error("items.$index.hasil")
                                        <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][keterangan]"
                                        value="{{ $oldKet }}"
                                        class="js-ket-input block w-full min-w-0 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-1.5 @error('items.'.$index.'.keterangan') border-red-500 @enderror"
                                        placeholder="Opsional"
                                        {{ $oldHasil === 'tidak_sesuai' ? 'required' : '' }}
                                    >
                                    @error("items.$index.keterangan")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                @else
                                <td class="px-2 py-2 text-gray-700 overflow-hidden">
                                    @if($detail->hasil_verifikasi === 'sesuai')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-900" title="Sesuai">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                            Sesuai
                                        </span>
                                    @elseif($detail->hasil_verifikasi === 'tidak_sesuai')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-900" title="Tidak sesuai">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                            Tidak sesuai
                                        </span>
                                    @endif
                                    @if(filled($detail->keterangan))
                                        <p class="mt-1 text-xs text-gray-700 break-words whitespace-normal">{{ $detail->keterangan }}</p>
                                    @elseif(!$detail->hasil_verifikasi)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                @php
                                    $colspan = 7 + ($hasFarmasiPersediaan ? 2 : 0) + ($hasAset ? 1 : 0) + ($canVerify ? 1 : 0);
                                @endphp
                                <td colspan="{{ $colspan }}" class="px-4 py-3 text-sm text-center text-gray-500">Tidak ada data detail penerimaan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                @if($canVerify)
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan verifikasi
                        </button>
                    </div>
                </form>
                @endif
            </div>

            @if($penerimaan->status_penerimaan === 'MENUNGGU_BUKTI_SAMPAI')
            <div class="border border-blue-200 rounded-lg bg-blue-50 p-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Menunggu bukti pengiriman</h3>
                <p class="text-sm text-gray-600 mb-3">Verifikasi klinik baru dapat dilakukan setelah pengirim mengunggah foto bukti dan nama penerima di detail distribusi (SBBK).</p>
                @if($penerimaan->distribusi)
                    <a href="{{ route('transaction.distribusi.show', $penerimaan->distribusi->id_distribusi) }}#bukti-sampai" class="inline-flex items-center text-sm font-medium text-blue-700 hover:text-blue-900">
                        Buka detail distribusi untuk isi bukti sampai →
                    </a>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('formVerifikasiDetail');
    if (!form) return;

    function styleRow(row, value) {
        const hidden = row.querySelector('.js-hasil-input');
        const ket = row.querySelector('.js-ket-input');
        const btnSesuai = row.querySelector('.js-btn-sesuai');
        const btnTidak = row.querySelector('.js-btn-tidak');
        if (!hidden || !btnSesuai || !btnTidak) return;

        hidden.value = value || '';
        const aktifSesuai = value === 'sesuai';
        const aktifTidak = value === 'tidak_sesuai';

        btnSesuai.className = 'js-btn-sesuai inline-flex h-7 w-7 shrink-0 items-center justify-center rounded border ' +
            (aktifSesuai
                ? 'border-green-600 bg-green-600 text-white'
                : 'border-green-300 bg-white text-green-700 hover:bg-green-50');
        btnTidak.className = 'js-btn-tidak inline-flex h-7 w-7 shrink-0 items-center justify-center rounded border ' +
            (aktifTidak
                ? 'border-red-600 bg-red-600 text-white'
                : 'border-red-300 bg-white text-red-700 hover:bg-red-50');

        if (ket) {
            if (aktifTidak) {
                ket.setAttribute('required', 'required');
                ket.placeholder = 'Wajib diisi (qty kurang, rusak, dll)';
            } else {
                ket.removeAttribute('required');
                ket.placeholder = 'Opsional';
            }
        }
    }

    form.querySelectorAll('.js-verifikasi-row').forEach(function (row) {
        const hidden = row.querySelector('.js-hasil-input');
        styleRow(row, hidden ? hidden.value : '');

        row.querySelector('.js-btn-sesuai')?.addEventListener('click', function () {
            styleRow(row, 'sesuai');
        });
        row.querySelector('.js-btn-tidak')?.addEventListener('click', function () {
            styleRow(row, 'tidak_sesuai');
            row.querySelector('.js-ket-input')?.focus();
        });
    });

    document.getElementById('btn_sesuai_semua')?.addEventListener('click', function () {
        form.querySelectorAll('.js-verifikasi-row').forEach(function (row) {
            styleRow(row, 'sesuai');
        });
    });

    form.addEventListener('submit', function (e) {
        let ok = true;
        form.querySelectorAll('.js-verifikasi-row').forEach(function (row) {
            const hasil = row.querySelector('.js-hasil-input')?.value;
            const ket = row.querySelector('.js-ket-input');
            if (!hasil) {
                ok = false;
            }
            if (hasil === 'tidak_sesuai' && ket && !String(ket.value || '').trim()) {
                ok = false;
                ket.focus();
            }
        });
        if (!ok) {
            e.preventDefault();
            alert('Pilih Sesuai / Tidak sesuai untuk setiap barang. Keterangan wajib jika Tidak sesuai.');
        }
    });
})();
</script>
@endpush

