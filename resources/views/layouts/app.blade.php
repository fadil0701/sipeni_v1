<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Select2 base (dimuat sebelum app.css agar tema SIPENI menimpa default) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Garis sel tabel daftar (index) di konten utama --}}
    <style>
        main table {
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            width: 100%;
            table-layout: auto;
        }
        main table > thead > tr > th,
        main table > thead > tr > td,
        main table > tbody > tr > th,
        main table > tbody > tr > td,
        main table > tfoot > tr > th,
        main table > tfoot > tr > td {
            border: 1px solid #e5e7eb;
            padding: 0.45rem 0.65rem;
            vertical-align: middle;
            line-height: 1.25rem;
        }

        /* Semua header tabel rata tengah */
        main table > thead > tr > th {
            text-align: center;
        }

        /* Aksi: kolom dibuat ringkas, tidak melebar berlebihan */
        main table :is(th, td):last-child {
            width: 1%;
            white-space: nowrap;
        }

        /* Konten aksi default dirapikan agar lebih hemat tinggi row */
        main table td form,
        main table td .inline-flex,
        main table td .flex {
            align-items: center;
        }

        /* Pagination: kunci warna agar tetap seperti mode siang */
        main nav[role="navigation"] span[aria-current="page"] span,
        main nav[role="navigation"] span[aria-current="page"] {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        main nav[role="navigation"] a,
        main nav[role="navigation"] span {
            color: #374151 !important;
            background-color: #ffffff !important;
            border-color: #d1d5db !important;
        }

        main nav[role="navigation"] a:hover {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border-color: #93c5fd !important;
        }

        main nav[role="navigation"] svg {
            color: #6b7280 !important;
        }
    </style>
</head>
<body
    class="font-sans antialiased bg-gray-100"
    data-features="@yield('layout_features', 'global-loading form-confirm choices-init action-icons')"
    data-table-mode="@yield('layout_table_mode', 'all')"
    data-sipeni-flash-toast="{{ config('sipeni.notifications.toast_mirror_flash', false) ? '1' : '0' }}"
>
    <script>
        (function () {
            window.__layoutEnabled = function (name) {
                var raw = (document.body && document.body.dataset.features) || '';
                var parts = raw.split(/\s+/).filter(Boolean);
                if (!parts.length) {
                    return true;
                }
                return parts.indexOf(name) !== -1;
            };
            window.__appDebug = false;
            window.__gLog = function () {};
            window.__gWarn = function () {
                if (window.__appDebug && typeof console !== 'undefined' && console.warn) {
                    console.warn.apply(console, arguments);
                }
            };
            window.__tableMode = (document.body && document.body.dataset.tableMode) || 'all';
        })();
    </script>
    <div class="min-h-screen flex">
        {{-- Backdrop mobile/tablet saat sidebar terbuka --}}
        <div
            id="sidebar-backdrop"
            class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-[1px] lg:hidden hidden"
            aria-hidden="true"
        ></div>

        <!-- Sidebar -->
        <aside
            id="app-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-[min(100vw-3rem,17rem)] max-w-[85vw] flex-shrink-0 -translate-x-full transform bg-blue-900 text-white transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:w-64 lg:max-w-none lg:translate-x-0"
            aria-label="Navigasi utama"
        >
            <div class="flex h-full w-full flex-col">
                <!-- Logo + tutup (mobile) -->
                <div class="flex items-center justify-between border-b border-blue-800 p-4">
                    <div class="flex min-w-0 items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto max-w-full sm:h-10">
                    </div>
                    <button
                        type="button"
                        id="sidebar-close"
                        class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-blue-200 hover:bg-blue-800 hover:text-white lg:hidden"
                        aria-label="Tutup menu"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6">
                    @php
                        use App\Helpers\PermissionHelper;
                        $accessibleMenus = $accessibleMenus ?? [];
                        $canAccessMasterManajemen = isset($accessibleMenus['master-manajemen']);
                        $canAccessMasterData = isset($accessibleMenus['master-data']);
                        $canAccessInventory = isset($accessibleMenus['inventory']);
                        $canAccessPermintaan = isset($accessibleMenus['permintaan']);
                        $canAccessApproval = isset($accessibleMenus['approval']);
                        $canAccessPengurusBarang = isset($accessibleMenus['pengurus-barang']);
                        $canAccessAsset = isset($accessibleMenus['aset-kir']);
                        $canAccessPlanning = isset($accessibleMenus['planning']);
                        $canAccessProcurement = isset($accessibleMenus['procurement']);
                        $canAccessMaintenance = isset($accessibleMenus['maintenance']);
                        $canAccessReports = isset($accessibleMenus['laporan']);
                    @endphp
                    @php
                        $isRoute = fn (array $patterns) => request()->routeIs(...$patterns);
                        $onRkuPages = $isRoute(['planning.rku.index', 'planning.rku.create', 'planning.rku.store', 'planning.rku.show', 'planning.rku.edit', 'planning.rku.update']);
                        $effectiveRkuContext = request('context');
                        if (! in_array($effectiveRkuContext, ['unit', 'daftar'], true)) {
                            $effectiveRkuContext = ($currentUser && \App\Support\Rbac\UserScope::mustScopeToUnitKerja($currentUser))
                                ? 'unit'
                                : 'daftar';
                        }
                        $isRkuUnitHighlight = $onRkuPages && $effectiveRkuContext === 'unit';
                        $isRkuDaftarHighlight = $onRkuPages && $effectiveRkuContext === 'daftar';
                        $canMenuRkuUnit = $currentUser
                            && PermissionHelper::canAccess($currentUser, 'planning.rku.index')
                            && (
                                // Unit kerja: admin_unit / kepala_unit / legacy unit
                                \App\Support\Rbac\UserScope::mustScopeToUnitKerja($currentUser)
                                // Super admin & bypass: boleh uji kedua pintu (Transaksi + Perencanaan)
                                || PermissionHelper::hasEnterpriseBypassRole($currentUser)
                                // Cadangan: punya form create + terhubung unit kerja (meski role pusat)
                                || (
                                    PermissionHelper::canAccess($currentUser, 'planning.rku.create')
                                    && (int) ($currentUser->pegawai?->id_unit_kerja ?? 0) > 0
                                    && ! $currentUser->hasPermission('planning.rku.view_all')
                                )
                            );
                        $canMenuRkuDaftar = $currentUser
                            && PermissionHelper::canAccess($currentUser, 'planning.rku.index')
                            && (
                                $currentUser->hasPermission('planning.rku.view_all')
                                || ! \App\Support\Rbac\UserScope::mustScopeToUnitKerja($currentUser)
                                || PermissionHelper::hasEnterpriseBypassRole($currentUser)
                            );
                        $isRkuInputRoute = $isRoute(['planning.rku.create', 'planning.rku.edit']);
                        $linkClass = fn (bool $active = false) => 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-150 '.($active ? 'bg-blue-600 text-white border-l-4 border-white shadow-md' : 'text-blue-200 hover:bg-blue-800');
                        $groupClass = fn (bool $open = false) => 'pl-4 mt-2 space-y-1 '.($open ? '' : 'hidden');
                        $showPlanningSidebar = $currentUser
                            && ($canAccessPlanning || $canAccessMasterManajemen || $canMenuRkuDaftar)
                            && (
                                PermissionHelper::canAccess($currentUser, 'master.program.index')
                                || PermissionHelper::canAccess($currentUser, 'master.kegiatan.index')
                                || PermissionHelper::canAccess($currentUser, 'master.sub-kegiatan.index')
                                || $canMenuRkuDaftar
                                || PermissionHelper::canAccess($currentUser, 'planning.rekap-tahunan')
                            );
                    @endphp

                    <!-- MAIN SECTION -->
                    <div class="mb-4">
                        <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            MAIN
                        </div>
                        <ul class="space-y-1">
                        <li>
                            <a href="{{ route('user.dashboard') }}" class="{{ $linkClass($isRoute(['user.dashboard'])) }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        @if($currentUser && \App\Services\PanduanPenggunaService::userCanAccess($currentUser))
                        <li>
                            <a href="{{ route('panduan.index') }}" class="{{ $linkClass($isRoute(['panduan.*'])) }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span>Panduan Pengguna</span>
                            </a>
                        </li>
                        @endif

                
                        @if($currentUser && ($canAccessPermintaan || PermissionHelper::canAccess($currentUser, 'user.requests.index') || $canMenuRkuUnit))
                            @php
                                $permintaanOpen = $isRoute(['transaction.permintaan-barang.*', 'maintenance.permintaan-pemeliharaan.*', 'user.requests.*', 'transaction.peminjaman-barang.*', 'transaction.pengembalian-barang.*']) || $isRkuUnitHighlight || ($isRkuInputRoute && $effectiveRkuContext === 'unit');
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('permintaan-unit')">
                                    <span>Transaksi</span>
                                    <svg id="permintaan-unit-arrow" class="w-4 h-4 ml-auto transition-transform {{ $permintaanOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="permintaan-unit-submenu" class="{{ $groupClass($permintaanOpen) }}">
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.permintaan-barang.index') || PermissionHelper::canAccess($currentUser, 'user.requests.index'))
                                        <li><a href="{{ route('transaction.permintaan-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.permintaan-barang.*', 'user.requests.*'])) }}">Permintaan Barang</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.permintaan-pemeliharaan.index'))
                                        <li><a href="{{ route('maintenance.permintaan-pemeliharaan.index') }}" class="{{ $linkClass($isRoute(['maintenance.permintaan-pemeliharaan.*'])) }}">Permintaan Pemeliharaan</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.peminjaman-barang.index'))
                                        <li><a href="{{ route('transaction.peminjaman-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.peminjaman-barang.*'])) }}">Peminjaman Barang</a></li>
                                        <li><a href="{{ route('transaction.pengembalian-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.pengembalian-barang.*'])) }}">Pengembalian Barang</a></li>
                                    @endif
                                    @if($canMenuRkuUnit)
                                        <li><a href="{{ route('planning.rku.index', ['context' => 'unit']) }}" class="{{ $linkClass($isRkuUnitHighlight) }}" title="Permintaan Rencana Kebutuhan Unit (unit sendiri)">RKU</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($canAccessApproval)
                            @php
                                $approvalOpen = $isRoute(['transaction.approval.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('approval')">
                                    <span>Approval</span>
                                    <svg id="approval-arrow" class="w-4 h-4 ml-auto transition-transform {{ $approvalOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="approval-submenu" class="{{ $groupClass($approvalOpen) }}">
                                    <li><a href="{{ route('transaction.approval.index') }}" class="{{ $linkClass($isRoute(['transaction.approval.index','transaction.approval.show'])) }}">Riwayat / Status Approval</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($showPlanningSidebar)
                            @php
                                $planningOpen = $isRoute(['planning.rekap-tahunan','master.program.*','master.kegiatan.*','master.sub-kegiatan.*']) || $isRkuDaftarHighlight;
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('planning')">
                                    <span>Perencanaan</span>
                                    <svg id="planning-arrow" class="w-4 h-4 ml-auto transition-transform {{ $planningOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="planning-submenu" class="{{ $groupClass($planningOpen) }}">
                                    @if(PermissionHelper::canAccess($currentUser, 'master.program.index'))
                                        <li><a href="{{ route('master.program.index') }}" class="{{ $linkClass($isRoute(['master.program.*'])) }}">Program</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'master.kegiatan.index'))
                                        <li><a href="{{ route('master.kegiatan.index') }}" class="{{ $linkClass($isRoute(['master.kegiatan.*'])) }}">Kegiatan</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'master.sub-kegiatan.index'))
                                        <li><a href="{{ route('master.sub-kegiatan.index') }}" class="{{ $linkClass($isRoute(['master.sub-kegiatan.*'])) }}">Sub Kegiatan</a></li>
                                    @endif
                                    @if($canMenuRkuDaftar)
                                        <li><a href="{{ route('planning.rku.index', ['context' => 'daftar']) }}" class="{{ $linkClass($isRkuDaftarHighlight) }}" title="Seluruh permintaan RKU lintas unit">Daftar RKU</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'planning.rekap-tahunan'))
                                        <li><a href="{{ route('planning.rekap-tahunan') }}" class="{{ $linkClass($isRoute(['planning.rekap-tahunan'])) }}">Rekap Tahunan</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($canAccessProcurement)
                            @php
                                $procOpen = $isRoute(['procurement.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('procurement')">
                                    <span>Pengadaan</span>
                                    <svg id="procurement-arrow" class="w-4 h-4 ml-auto transition-transform {{ $procOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="procurement-submenu" class="{{ $groupClass($procOpen) }}">
                                    <li><a href="{{ route('procurement.paket-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.paket-pengadaan.*'])) }}">Paket Pengadaan</a></li>
                                    <li><a href="{{ route('procurement.proses-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.proses-pengadaan.*'])) }}">Paket Berjalan</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessPengurusBarang)
                            @php
                                $distOpen = $isRoute(['transaction.draft-distribusi.*','transaction.distribusi.*','transaction.penerimaan-barang.*','transaction.retur-barang.*','transaction.pemakaian-barang.*','transaction.compile-distribusi.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('distribusi')">
                                    <span>Distribusi Barang</span>
                                    <svg id="distribusi-arrow" class="w-4 h-4 ml-auto transition-transform {{ $distOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="distribusi-submenu" class="{{ $groupClass($distOpen) }}">
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.draft-distribusi.index'))
                                        <li><a href="{{ route('transaction.draft-distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.draft-distribusi.*'])) }}">Daftar Permintaan</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.distribusi.index'))
                                        <li><a href="{{ route('transaction.distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.distribusi.*'])) }}">Distribusi Barang (SBBK)</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.penerimaan-barang.index'))
                                        <li><a href="{{ route('transaction.penerimaan-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.penerimaan-barang.*'])) }}">Penerimaan Barang</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.retur-barang.index'))
                                        <li><a href="{{ route('transaction.retur-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.retur-barang.*'])) }}">Retur Barang Rusak</a></li>
                                    @endif
                                    {{-- Modul Pemakaian Barang dinonaktifkan --}}
                                </ul>
                            </li>
                        @endif

                        @php
                            $invMenuUser = $currentUser ?? auth()->user();
                            $invSidebarPerms = [
                                'master-data.satuan.index',
                                'master-data.sumber-anggaran.index',
                                'master.gudang.index',
                                'master-data.aset.index',
                                'master-data.kode-barang.index',
                                'master-data.kategori-barang.index',
                                'master-data.jenis-barang.index',
                                'master-data.subjenis-barang.index',
                                'master-data.data-barang.index',
                                'master-data.import-struktur-barang.index',
                                'inventory.data-inventory.index',
                                'inventory.data-inventory.import.*',
                                'inventory.inventory-item.*',
                                'inventory.data-stock.index',
                                'reports.kartu-stok',
                                'inventory.stock-adjustment.index',
                                'inventory.farmasi-kedaluwarsa.index',
                            ];
                            $showInventorySidebarGroup = false;
                            if ($invMenuUser) {
                                foreach ($invSidebarPerms as $__p) {
                                    if (PermissionHelper::canAccess($invMenuUser, $__p)) {
                                        $showInventorySidebarGroup = true;
                                        break;
                                    }
                                }
                            }
                            $canInvLink = $invMenuUser
                                ? fn (string $perm): bool => PermissionHelper::canAccess($invMenuUser, $perm)
                                : fn (string $perm): bool => false;
                        @endphp
                        @if($showInventorySidebarGroup)
                            @php
                                $inventoryOpen = $isRoute(['inventory.*','master-data.*','master.gudang.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('inventory')">
                                    <span>Inventory</span>
                                    <svg id="inventory-arrow" class="w-4 h-4 ml-auto transition-transform {{ $inventoryOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="inventory-submenu" class="{{ $groupClass($inventoryOpen) }}">
                                    @if($canInvLink('master-data.satuan.index') || $canInvLink('master-data.sumber-anggaran.index') || $canInvLink('master.gudang.index'))
                                        <li class="px-4 pt-2 text-[11px] uppercase tracking-wide text-blue-300">Master Data</li>
                                    @endif
                                    @if($canInvLink('master-data.satuan.index'))
                                        <li><a href="{{ route('master-data.satuan.index') }}" class="{{ $linkClass($isRoute(['master-data.satuan.*'])) }}">Satuan</a></li>
                                    @endif
                                    @if($canInvLink('master-data.sumber-anggaran.index'))
                                        <li><a href="{{ route('master-data.sumber-anggaran.index') }}" class="{{ $linkClass($isRoute(['master-data.sumber-anggaran.*'])) }}">Sumber Anggaran</a></li>
                                    @endif
                                    @if($canInvLink('master.gudang.index'))
                                        <li><a href="{{ route('master.gudang.index') }}" class="{{ $linkClass($isRoute(['master.gudang.*'])) }}">Gudang</a></li>
                                    @endif

                                    @if($canInvLink('master-data.aset.index') || $canInvLink('master-data.kode-barang.index') || $canInvLink('master-data.kategori-barang.index') || $canInvLink('master-data.jenis-barang.index') || $canInvLink('master-data.subjenis-barang.index') || $canInvLink('master-data.data-barang.index') || $canInvLink('master-data.import-struktur-barang.index'))
                                        <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Struktur Barang</li>
                                    @endif
                                    @if($canInvLink('master-data.aset.index'))
                                        <li><a href="{{ route('master-data.aset.index') }}" class="{{ $linkClass($isRoute(['master-data.aset.*'])) }}">Klasifikasi Aset</a></li>
                                    @endif
                                    @if($canInvLink('master-data.kode-barang.index'))
                                        <li><a href="{{ route('master-data.kode-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.kode-barang.*'])) }}">Kode Barang</a></li>
                                    @endif
                                    @if($canInvLink('master-data.kategori-barang.index'))
                                        <li><a href="{{ route('master-data.kategori-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.kategori-barang.*'])) }}">Kategori Barang</a></li>
                                    @endif
                                    @if($canInvLink('master-data.jenis-barang.index'))
                                        <li><a href="{{ route('master-data.jenis-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.jenis-barang.*'])) }}">Jenis Barang</a></li>
                                    @endif
                                    @if($canInvLink('master-data.subjenis-barang.index'))
                                        <li><a href="{{ route('master-data.subjenis-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.subjenis-barang.*'])) }}">Subjenis Barang</a></li>
                                    @endif
                                    @if($canInvLink('master-data.data-barang.index'))
                                        <li><a href="{{ route('master-data.data-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.data-barang.*'])) }}">Data Barang</a></li>
                                    @endif
                                    @if($canInvLink('master-data.import-struktur-barang.index'))
                                        <li><a href="{{ route('master-data.import-struktur-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.import-struktur-barang.*'])) }}">Import Struktur Barang</a></li>
                                    @endif

                                    @if($canInvLink('inventory.data-inventory.index') || $canInvLink('inventory.data-inventory.import.*') || $canInvLink('inventory.inventory-item.*'))
                                        <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Data Inventory</li>
                                    @endif
                                    @if($canInvLink('inventory.data-inventory.index'))
                                        <li><a href="{{ route('inventory.data-inventory.index') }}" class="{{ $linkClass($isRoute(['inventory.data-inventory.*']) && ! $isRoute(['inventory.data-inventory.import.*'])) }}">Data Inventory</a></li>
                                    @endif
                                    @if($canInvLink('inventory.data-inventory.import.*'))
                                        <li><a href="{{ route('inventory.data-inventory.import.index') }}" class="{{ $linkClass($isRoute(['inventory.data-inventory.import.*'])) }}">Import Data Inventory</a></li>
                                    @endif
                                    @if($canInvLink('inventory.inventory-item.*'))
                                        <li><a href="{{ route('inventory.scan-qr') }}" class="{{ $linkClass($isRoute(['inventory.scan-qr'])) }}">Scan QR Code</a></li>
                                    @endif

                                    @if($canInvLink('inventory.data-stock.index') || $canInvLink('reports.kartu-stok') || $canInvLink('inventory.stock-adjustment.index') || $canInvLink('inventory.farmasi-kedaluwarsa.index'))
                                        <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Stok &amp; Transaksi</li>
                                    @endif
                                    @if($canInvLink('inventory.data-stock.index'))
                                        <li><a href="{{ route('inventory.data-stock.index') }}" class="{{ $linkClass($isRoute(['inventory.data-stock.*'])) }}">Data Stock</a></li>
                                    @endif
                                    @if($canInvLink('reports.kartu-stok'))
                                        <li><a href="{{ route('reports.kartu-stok') }}" class="{{ $linkClass($isRoute(['reports.kartu-stok', 'reports.kartu-stok.merk-breakdown'])) }}">Kartu Stok</a></li>
                                    @endif
                                    @if($canInvLink('inventory.stock-adjustment.index'))
                                        <li><a href="{{ route('inventory.stock-adjustment.index') }}" class="{{ $linkClass($isRoute(['inventory.stock-adjustment.*'])) }}">Stock Adjustment / Opname</a></li>
                                    @endif
                                    @if($canInvLink('inventory.farmasi-kedaluwarsa.index'))
                                        <li><a href="{{ route('inventory.farmasi-kedaluwarsa.index') }}" class="{{ $linkClass($isRoute(['inventory.farmasi-kedaluwarsa.*'])) }}">Reminder tanggal kedaluwarsa (stok)</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($canAccessAsset)
                            @php
                                $assetOpen = $isRoute(['asset.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('aset-kir')">
                                    <span>Aset</span>
                                    <svg id="aset-kir-arrow" class="w-4 h-4 ml-auto transition-transform {{ $assetOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="aset-kir-submenu" class="{{ $groupClass($assetOpen) }}">
                                    <li><a href="{{ route('asset.register-aset.index') }}" class="{{ $linkClass($isRoute(['asset.register-aset.*'])) }}">Register Aset & Rincian</a></li>
                                    <li><a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="{{ $linkClass($isRoute(['asset.kartu-inventaris-ruangan.*'])) }}">Dokumen KIR (Cetak)</a></li>
                                    <li><a href="{{ route('asset.mutasi-aset.index') }}" class="{{ $linkClass($isRoute(['asset.mutasi-aset.*'])) }}">Mutasi Aset</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessMaintenance)
                            @php
                                $maintOpen = $isRoute(['maintenance.daftar-permintaan-pemeliharaan.*', 'maintenance.jadwal-maintenance.*', 'maintenance.kalibrasi-aset.*', 'maintenance.service-report.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('maintenance')">
                                    <span>Pemeliharaan</span>
                                    <svg id="maintenance-arrow" class="w-4 h-4 ml-auto transition-transform {{ $maintOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="maintenance-submenu" class="{{ $groupClass($maintOpen) }}">
                                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.daftar-permintaan-pemeliharaan.index')
                                        || PermissionHelper::canAccess($currentUser, 'maintenance.service-report.create'))
                                        <li><a href="{{ route('maintenance.daftar-permintaan-pemeliharaan.index') }}" class="{{ $linkClass($isRoute(['maintenance.daftar-permintaan-pemeliharaan.*'])) }}">Daftar Permintaan</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.jadwal-maintenance.index'))
                                        <li><a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="{{ $linkClass($isRoute(['maintenance.jadwal-maintenance.*'])) }}">Jadwal Pemeliharaan</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.kalibrasi-aset.index'))
                                        <li><a href="{{ route('maintenance.kalibrasi-aset.index') }}" class="{{ $linkClass($isRoute(['maintenance.kalibrasi-aset.*'])) }}">Kalibrasi</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.service-report.index'))
                                        <li><a href="{{ route('maintenance.service-report.index') }}" class="{{ $linkClass($isRoute(['maintenance.service-report.*'])) }}">Laporan Servis</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- Menu Keuangan/Pembayaran disembunyikan: controller masih stub (lihat docs/PERBAIKAN_AUDIT_UI_CETAK_2026-07-24.md) --}}

                        @if($canAccessReports)
                            @php
                                $reportOpen = $isRoute(['reports.*','report.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('laporan')">
                                    <span>Monitoring</span>
                                    <svg id="laporan-arrow" class="w-4 h-4 ml-auto transition-transform {{ $reportOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="laporan-submenu" class="{{ $groupClass($reportOpen) }}">
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass($isRoute(['reports.index'])) }}">Ringkasan Laporan</a></li>
                                    <li><a href="{{ route('reports.stock-gudang') }}" class="{{ $linkClass($isRoute(['reports.stock-gudang', 'reports.stock-gudang.export'])) }}">Laporan Stok Gudang</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessMasterManajemen)
                            @php
                                $masterOpen = $isRoute(['master-manajemen.*','master.unit-kerja.*','master.ruangan.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('master-management')">
                                    <span>Organisasi</span>
                                    <svg id="master-management-arrow" class="w-4 h-4 ml-auto transition-transform {{ $masterOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="master-management-submenu" class="{{ $groupClass($masterOpen) }}">
                                    <li><a href="{{ route('master-manajemen.master-pegawai.index') }}" class="{{ $linkClass($isRoute(['master-manajemen.master-pegawai.*'])) }}">Pegawai</a></li>
                                    <li><a href="{{ route('master-manajemen.master-jabatan.index') }}" class="{{ $linkClass($isRoute(['master-manajemen.master-jabatan.*'])) }}">Jabatan</a></li>
                                    <li><a href="{{ route('master.unit-kerja.index') }}" class="{{ $linkClass($isRoute(['master.unit-kerja.*'])) }}">Unit Kerja</a></li>
                                    <li><a href="{{ route('master.ruangan.index') }}" class="{{ $linkClass($isRoute(['master.ruangan.*'])) }}">Ruangan</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($currentUser && PermissionHelper::canAccess($currentUser, 'admin.*'))
                            <div class="mb-3 mt-6">
                                <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    System Administration
                                </div>
                            @php
                                $adminOpen = $isRoute(['admin.*']);
                            @endphp
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('admin')">
                                    <span>Akses &amp; Kontrol</span>
                                    <svg id="admin-arrow" class="w-4 h-4 ml-auto transition-transform {{ $adminOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="admin-submenu" class="{{ $groupClass($adminOpen) }}">
                                    <li><a href="{{ route('admin.users.index') }}" class="{{ $linkClass($isRoute(['admin.users.*'])) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        User &amp; Account Directory
                                    </a></li>
                                    <li><a href="{{ route('admin.roles.index') }}" class="{{ $linkClass($isRoute(['admin.roles.*'])) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Role &amp; Workflow Authority
                                    </a></li>
                                    @if(config('sipeni.feature_print_templates') && PermissionHelper::canAccess($currentUser, 'admin.print-templates.index'))
                                        <li><a href="{{ route('admin.print-templates.index') }}" class="{{ $linkClass($isRoute(['admin.print-templates.*'])) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                        Workflow Template
                                    </a></li>
                                    @endif
                                    <li><a href="{{ route('admin.audit-trail.index') }}" class="{{ $linkClass($isRoute(['admin.audit-trail.*'])) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Executive Activity Timeline
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="relative flex min-w-0 flex-1 flex-col overflow-hidden">
            <div id="global-loading-bar" class="pointer-events-none fixed left-0 top-0 z-[10000] h-0.5 w-0 bg-blue-600 transition-all duration-300"></div>
            <!-- Header -->
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white">
                <div class="px-3 py-3 sm:px-6 sm:py-4">
                    <div class="flex items-center justify-between gap-2 sm:gap-4">
                        <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                            <button
                                type="button"
                                id="sidebar-toggle"
                                class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 lg:hidden"
                                aria-label="Buka menu navigasi"
                                aria-expanded="false"
                                aria-controls="app-sidebar"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <div class="min-w-0">
                                <h1 class="truncate text-base font-bold text-gray-900 sm:text-xl">{{ config('app.name') }}</h1>
                                <p class="mt-0.5 hidden truncate text-xs text-gray-500 sm:block">SISTEM MANAJEMEN &amp; INVENTORY TERINTEGRASI</p>
                            </div>
                        </div>
                        <div class="flex flex-shrink-0 items-center gap-1 sm:gap-4">
                            <!-- Notifications (desktop/tablet landscape) -->
                            <div class="hidden items-center gap-1 md:flex">
                            <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900" aria-label="Notifikasi">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @php $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp
                                @if($unreadCount > 0)
                                    <span class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white ring-2 ring-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                @endif
                            </a>
                            </div>
                            <!-- User Menu -->
                            <div class="relative">
                                <button 
                                    type="button" 
                                    id="user-menu-button"
                                    onclick="toggleUserMenu()"
                                    class="flex items-center gap-2 rounded-lg text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:gap-3 sm:p-1"
                                >
                                    <x-enterprise.avatar :user="$currentUser" size="md" />
                                    <div class="hidden min-w-0 sm:block">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ $currentUser?->name ?? 'User' }}</p>
                                        <p class="truncate text-xs text-gray-500">
                                            @if($userPrimaryRole)
                                                {{ $userPrimaryRole->display_name }}
                                            @else
                                                User
                                            @endif
                                        </p>
                                    </div>
                                    <svg id="user-menu-arrow" class="hidden h-5 w-5 flex-shrink-0 text-gray-400 transition-transform sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div 
                                    id="user-dropdown-menu"
                                    class="hidden absolute right-0 z-50 mt-2 w-48 max-w-[calc(100vw-2rem)] rounded-md border border-gray-200 bg-white py-1 shadow-lg"
                                >
                                    <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Profil Saya
                                        </div>
                                    </a>
                                    <a href="{{ route('profile.index') }}#password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Pengaturan Akun
                                        </div>
                                    </a>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                        >
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Logout
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="p-3 sm:p-4 lg:p-6">
                    <x-ui.flash-messages />
                    @php
                        $toastMirror = config('sipeni.notifications.toast_mirror_flash', false);
                        $toastMs = (int) config('sipeni.notifications.toast_default_ms', 4500);
                        $flashPayload = null;
                        if ($toastMirror) {
                            if (session()->has('success')) {
                                $flashPayload = ['type' => 'success', 'message' => session('success'), 'durationMs' => $toastMs];
                            } elseif (session()->has('error')) {
                                $flashPayload = ['type' => 'error', 'message' => session('error'), 'durationMs' => $toastMs];
                            } elseif (session()->has('warning')) {
                                $flashPayload = ['type' => 'warning', 'message' => session('warning'), 'durationMs' => $toastMs];
                            } elseif (session()->has('info')) {
                                $flashPayload = ['type' => 'info', 'message' => session('info'), 'durationMs' => $toastMs];
                            }
                        }
                    @endphp
                    @if ($flashPayload)
                        <script type="application/json" id="sipeni-flash-json">@json($flashPayload)</script>
                    @endif
                    @yield('content')
                </div>
            </main>

            <footer class="flex-shrink-0 border-t border-gray-200 bg-white">
                <div class="px-3 py-3 text-center sm:px-6">
                    <p class="text-xs font-medium text-gray-700">SIMANTIK-PPKP Versi 1.01.1</p>
                    <p class="mt-0.5 text-[11px] leading-snug text-gray-500">
                        Copyright &copy; 2026 Pusat Pelayanan Kesehatan Pegawai DKI Jakarta
                    </p>
                </div>
            </footer>
        </div>
    </div>

    <div
        id="global-loading-overlay"
        class="sipeni-loading-overlay"
        aria-live="polite"
        aria-busy="false"
        aria-hidden="true"
        role="status"
    >
        <div class="sipeni-loading-overlay__card">
            <svg class="sipeni-loading-overlay__spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10" class="opacity-20" stroke="currentColor" stroke-width="4"></circle>
                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
            </svg>
            <p class="sipeni-loading-overlay__text">Memproses data, mohon tunggu...</p>
        </div>
    </div>

    @stack('scripts')

    <x-ui.confirm-modal />

    <div id="sipeni-toast-stack" class="sipeni-toast-stack" aria-live="polite"></div>

    <!-- jQuery + Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- Script layout dipindah ke resources/js/layout/app-layout.js --}}
</body>
</html>
