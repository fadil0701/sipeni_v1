@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.approval.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Persetujuan
    </a>
</div>

@if($errors->any())
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <ul class="text-sm text-red-700 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $user = auth()->user();
    $stepOrder = $currentFlow?->step_order ?? 0;
    $statusColor = \App\Support\UiColor::badgeForStatus($displayStatus);
    $jenisPelaksanaOptions = \App\Enums\PemeliharaanJenisPelaksana::cases();
    $rekomendasiAkhir = $permintaan->rekomendasi_akhir ? (string) $permintaan->rekomendasi_akhir : null;
    $isPendingSparepart = $rekomendasiAkhir === \App\Enums\PemeliharaanRekomendasi::PendingSparepart->value;
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-semibold text-gray-900">Detail Permintaan Pemeliharaan</h2>
            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Pemeliharaan</span>
        </div>
        <p class="text-sm text-gray-600 mt-1">No. Permintaan: <span class="font-semibold">{{ $permintaan->no_permintaan_pemeliharaan }}</span></p>
    </div>

    <div class="p-6 space-y-6">
        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Status Approval</dt>
                <dd><span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">{{ $displayStatus }}</span></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Status Dokumen</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->status_permintaan }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Pemohon</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->pemohon->nama_pegawai ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Register / Barang</dt>
                <dd class="text-sm font-semibold text-gray-900">
                    {{ $permintaan->registerAset->nomor_register ?? '-' }}
                    <div class="text-xs text-gray-500">{{ $permintaan->registerAset->inventory->dataBarang->nama_barang ?? '' }}</div>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Jenis / Prioritas</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->jenis_pemeliharaan }} / {{ $permintaan->prioritas }}</dd>
            </div>
            @if($permintaan->jenis_pelaksana)
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Pelaksana</dt>
                    <dd class="text-sm font-semibold text-gray-900">
                        {{ $permintaan->jenis_pelaksana }}
                        @if($permintaan->pegawaiPelaksana)
                            — {{ $permintaan->pegawaiPelaksana->nama_pegawai }}
                        @endif
                        @if($permintaan->nama_vendor)
                            — {{ $permintaan->nama_vendor }}
                        @endif
                    </dd>
                </div>
            @endif
            @if($permintaan->rekomendasi_akhir)
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Rekomendasi Akhir</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $permintaan->rekomendasi_akhir }}</dd>
                </div>
            @endif
        </dl>

        @if($permintaan->deskripsi_kerusakan)
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-1">Deskripsi Kerusakan</h3>
                <p class="text-sm text-gray-900 whitespace-pre-wrap bg-gray-50 border border-gray-200 rounded-md p-3">{{ $permintaan->deskripsi_kerusakan }}</p>
            </div>
        @endif

        @if($permintaan->foto_kondisi)
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-1">Foto Kondisi</h3>
                <a href="{{ route('media.show', ['path' => $permintaan->foto_kondisi]) }}" target="_blank" rel="noopener">
                    <img src="{{ route('media.show', ['path' => $permintaan->foto_kondisi]) }}" alt="Foto kondisi" class="max-h-48 rounded-md border border-gray-200">
                </a>
            </div>
        @endif

        @if($permintaan->serviceReport)
            <div class="rounded-md border border-blue-100 bg-blue-50 p-3 text-sm">
                Service Report:
                <a class="font-semibold text-blue-700 hover:underline" href="{{ route('maintenance.service-report.show', $permintaan->serviceReport->id_service_report) }}">
                    {{ $permintaan->serviceReport->no_service_report }}
                </a>
                @if($permintaan->serviceReport->rekomendasi)
                    — Rekomendasi: {{ $permintaan->serviceReport->rekomendasi }}
                @endif
            </div>
        @elseif($permintaan->status_permintaan === 'DIPROSES')
            <div class="rounded-md border border-amber-100 bg-amber-50 p-3 text-sm">
                Menunggu Service Report.
                <a class="font-semibold text-amber-800 hover:underline" href="{{ route('maintenance.service-report.create', ['permintaan_id' => $permintaan->id_permintaan_pemeliharaan]) }}">Buat Service Report</a>
            </div>
        @endif
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Riwayat Persetujuan</h2>
    </div>
    <div class="p-6">
        <ul class="space-y-4">
            @forelse($approvalHistory as $hist)
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold {{ \App\Support\UiColor::badgeForStatus($hist->status) }}">
                        {{ $hist->approvalFlow->step_order ?? '-' }}
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $hist->approvalFlow->nama_step ?? 'Step' }} — {{ $hist->status }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $hist->approvalFlow?->role?->name ?? 'sistem' }}
                            @if($hist->user) · {{ $hist->user->name }} @endif
                            @if($hist->approved_at) · {{ $hist->approved_at->format('d/m/Y H:i') }} @endif
                        </p>
                        @if($hist->catatan)
                            <p class="mt-1 text-sm text-gray-700">{{ $hist->catatan }}</p>
                        @endif
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500">Belum ada riwayat approval.</li>
            @endforelse
        </ul>
    </div>
</div>

@if($approval->status === 'MENUNGGU' && ! $rejectedApproval)
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Aksi Persetujuan</h2>
            <p class="text-sm text-gray-600 mt-1">Step saat ini: {{ $currentFlow->nama_step ?? '-' }}</p>
        </div>
        <div class="p-6 space-y-4">
            {{-- Step 2 / 6 / 7: Diketahui --}}
            @if(in_array($stepOrder, [2, 6, 7], true) && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.mengetahui'))
                <form method="POST" action="{{ route('transaction.approval.mengetahui', $approval->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="catatan" rows="3" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <x-ui.btn action="mengetahui" type="submit">Ketahui</x-ui.btn>
                </form>

            {{-- Step 3 / 8 / 9: Kepala Pusat setujui/tolak --}}
            @elseif(in_array($stepOrder, [3, 8, 9], true) && ($currentFlow?->role?->name === 'kepala_pusat') && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.approve'))
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <form method="POST" action="{{ route('transaction.approval.approve', $approval->id) }}" class="space-y-4" data-confirm="Proses persetujuan ini?">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="3" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <x-ui.btn action="setujui" type="submit">
                            @if($stepOrder === 3) Setujui & Disposisi ke Pengurus Barang
                            @elseif($stepOrder === 8)
                                @if($isPendingSparepart) Setujui Pembelian
                                @else Ketahui (Selesai)
                                @endif
                            @else Setujui Pembelian
                            @endif
                        </x-ui.btn>
                    </form>
                    @if(!($stepOrder === 8 && ! $isPendingSparepart) && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.reject'))
                        <form method="POST" action="{{ route('transaction.approval.reject', $approval->id) }}" class="space-y-4" data-confirm="Tolak permintaan ini?">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Penolakan <span class="text-red-500">*</span></label>
                                <textarea name="catatan" rows="3" required minlength="10" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                            </div>
                            <x-ui.btn action="tolak" type="submit">Tolak</x-ui.btn>
                        </form>
                    @endif
                </div>

            {{-- Step 4: Disposisi Pengurus Barang --}}
            @elseif($stepOrder === 4 && \App\Helpers\PermissionHelper::canAccess($user, 'transaction.approval.disposisi'))
                <form method="POST" action="{{ route('transaction.approval.disposisi-pemeliharaan', $approval->id) }}" class="space-y-4" id="formDisposisiPemeliharaan">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pelaksana <span class="text-red-500">*</span></label>
                        <select name="jenis_pelaksana" id="jenis_pelaksana" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih jenis</option>
                            @foreach($jenisPelaksanaOptions as $opt)
                                <option value="{{ $opt->value }}">{{ $opt->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="field-pegawai">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai Pelaksana</label>
                        <select name="id_pegawai_pelaksana" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih pegawai</option>
                            @foreach($pegawaiPelaksanaOptions as $peg)
                                <option value="{{ $peg->id }}">{{ $peg->nama_pegawai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="field-vendor" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Vendor / Kontrak Service</label>
                        <input type="text" name="nama_vendor" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Nama vendor / kontraktor">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Disposisi</label>
                        <textarea name="disposisi_catatan" rows="3" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <x-ui.btn action="disposisi" type="submit">Disposisi ke Pelaksana</x-ui.btn>
                </form>
                @push('scripts')
                <script>
                document.getElementById('jenis_pelaksana')?.addEventListener('change', function () {
                    const vendor = ['KONTRAK_SERVICE', 'VENDOR'].includes(this.value);
                    document.getElementById('field-vendor')?.classList.toggle('hidden', !vendor);
                    document.getElementById('field-pegawai')?.classList.toggle('hidden', vendor);
                });
                </script>
                @endpush
            @else
                <p class="text-sm text-gray-600">Menunggu aksi dari role: <strong>{{ $currentFlow?->role?->name ?? '-' }}</strong></p>
            @endif
        </div>
    </div>
@endif
@endsection
