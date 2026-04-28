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

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Garis sel tabel daftar (index) di konten utama --}}
    <style>
        main table {
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
        }
        main table > thead > tr > th,
        main table > thead > tr > td,
        main table > tbody > tr > th,
        main table > tbody > tr > td,
        main table > tfoot > tr > th,
        main table > tfoot > tr > td {
            border: 1px solid #e5e7eb;
        }
    </style>
    
    <!-- Select2 placeholder dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body
    class="font-sans antialiased bg-gray-100"
    data-app-debug="{{ config('app.debug') ? '1' : '0' }}"
    data-features="@yield('layout_features', 'global-loading form-confirm choices-init action-icons')"
    data-table-mode="@yield('layout_table_mode', 'all')"
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
            window.__appDebug = (document.body && document.body.dataset.appDebug) === '1';
            window.__gLog = function () {
                if (window.__appDebug && typeof console !== 'undefined' && console.log) {
                    console.log.apply(console, arguments);
                }
            };
            window.__gWarn = function () {
                if (typeof console !== 'undefined' && console.warn) {
                    console.warn.apply(console, arguments);
                }
            };
            window.__tableMode = (document.body && document.body.dataset.tableMode) || 'all';
        })();
    </script>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-900 text-white flex-shrink-0">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-4 border-b border-blue-800">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto">
                    </div>
                </div>

                <!-- Navigation: gunakan variabel shared dari AppServiceProvider (currentUser, accessibleMenus, userRoles, userRoleIds, userPrimaryRole) -->
                <nav class="flex-1 overflow-y-auto p-4">
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
                        $canAccessFinance = isset($accessibleMenus['finance']);
                        $canAccessMaintenance = isset($accessibleMenus['maintenance']);
                        $canAccessReports = isset($accessibleMenus['laporan']);
                    @endphp
                    @php
                        $isRoute = fn (array $patterns) => request()->routeIs(...$patterns);
                        $linkClass = fn (bool $active = false) => 'flex items-center px-4 py-2 rounded-lg text-sm '.($active ? 'bg-blue-700 text-white' : 'text-blue-200 hover:bg-blue-800');
                        $groupClass = fn (bool $open = false) => 'pl-4 mt-2 space-y-1 '.($open ? '' : 'hidden');
                    @endphp

                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('user.dashboard') }}" class="{{ $linkClass($isRoute(['user.dashboard'])) }}">
                                <span>Dashboard</span>
                            </a>
                        </li>

                
                        @if($currentUser && ($canAccessPermintaan || PermissionHelper::canAccess($currentUser, 'user.requests.index')))
                            @php($permintaanOpen = $isRoute(['transaction.permintaan-barang.*', 'maintenance.permintaan-pemeliharaan.*', 'planning.rku.*', 'user.requests.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('permintaan-unit')">
                                    <span>Transaksi</span>
                                    <svg id="permintaan-unit-arrow" class="w-4 h-4 ml-auto transition-transform {{ $permintaanOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="permintaan-unit-submenu" class="{{ $groupClass($permintaanOpen) }}">
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.permintaan-barang.index'))
                                        <li><a href="{{ route('transaction.permintaan-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.permintaan-barang.*'])) }}">Permintaan Barang</a></li>
                                    @elseif(PermissionHelper::canAccess($currentUser, 'user.requests.index'))
                                        <li><a href="{{ route('user.requests.index') }}" class="{{ $linkClass($isRoute(['user.requests.*'])) }}">Permintaan Barang</a></li>
                                    @endif                                   
                                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.permintaan-pemeliharaan.index'))
                                        <li><a href="{{ route('maintenance.permintaan-pemeliharaan.index') }}" class="{{ $linkClass($isRoute(['maintenance.permintaan-pemeliharaan.*'])) }}">Permintaan Pemeliharaan</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'transaction.peminjaman-barang.index'))
                                        <li><a href="{{ route('transaction.peminjaman-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.peminjaman-barang.*'])) }}">Peminjaman Barang</a></li>
                                        <li><a href="{{ route('transaction.pengembalian-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.pengembalian-barang.*'])) }}">Pengembalian Barang</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'planning.rku.index'))
                                        <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass($isRoute(['planning.rku.*'])) }}">Input RKU</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($canAccessApproval)
                            @php($approvalOpen = $isRoute(['transaction.approval.*']))
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

                        @if($currentUser && (
                            ($canAccessPlanning || $canAccessMasterManajemen) && (
                                PermissionHelper::canAccess($currentUser, 'master.program.index')
                                || PermissionHelper::canAccess($currentUser, 'master.kegiatan.index')
                                || PermissionHelper::canAccess($currentUser, 'master.sub-kegiatan.index')
                                || PermissionHelper::canAccess($currentUser, 'planning.rku.index')
                                || PermissionHelper::canAccess($currentUser, 'planning.rekap-tahunan')
                            )
                        ))
                            @php($planningOpen = $isRoute(['planning.*','master.program.*','master.kegiatan.*','master.sub-kegiatan.*']))
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
                                    @if(PermissionHelper::canAccess($currentUser, 'planning.rku.index'))
                                        <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass($isRoute(['planning.rku.*'])) }}">RKU &amp; Aktivitas</a></li>
                                    @endif
                                    @if(PermissionHelper::canAccess($currentUser, 'planning.rekap-tahunan'))
                                        <li><a href="{{ route('planning.rekap-tahunan') }}" class="{{ $linkClass($isRoute(['planning.rekap-tahunan'])) }}">Rekap Tahunan</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($canAccessProcurement)
                            @php($procOpen = $isRoute(['procurement.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('procurement')">
                                    <span>Pengadaan</span>
                                    <svg id="procurement-arrow" class="w-4 h-4 ml-auto transition-transform {{ $procOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="procurement-submenu" class="{{ $groupClass($procOpen) }}">
                                    <li><a href="{{ route('procurement.paket-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.paket-pengadaan.*'])) }}">Paket Pengadaan</a></li>
                                    <li><a href="{{ route('procurement.proses-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.proses-pengadaan.*'])) }}">Proses &amp; Realisasi Pengadaan</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessPengurusBarang)
                            @php($distOpen = $isRoute(['transaction.draft-distribusi.*','transaction.distribusi.*','transaction.penerimaan-barang.*','transaction.retur-barang.*','transaction.pemakaian-barang.*','transaction.compile-distribusi.*']))
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

                        @if($canAccessInventory || $canAccessMasterData)
                            @php($inventoryOpen = $isRoute(['inventory.*','master-data.*','master.gudang.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('inventory')">
                                    <span>Inventory</span>
                                    <svg id="inventory-arrow" class="w-4 h-4 ml-auto transition-transform {{ $inventoryOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="inventory-submenu" class="{{ $groupClass($inventoryOpen) }}">
                                    <li class="px-4 pt-2 text-[11px] uppercase tracking-wide text-blue-300">Master Data</li>
                                        <li><a href="{{ route('master-data.satuan.index') }}" class="{{ $linkClass($isRoute(['master-data.satuan.*'])) }}">Satuan</a></li>
                                        <li><a href="{{ route('master-data.sumber-anggaran.index') }}" class="{{ $linkClass($isRoute(['master-data.sumber-anggaran.*'])) }}">Sumber Anggaran</a></li>
                                        <li><a href="{{ route('master.gudang.index') }}" class="{{ $linkClass($isRoute(['master.gudang.*'])) }}">Gudang</a></li>

                                    <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Struktur Barang</li>
                                        <li><a href="{{ route('master-data.aset.index') }}" class="{{ $linkClass($isRoute(['master-data.aset.*'])) }}">Klasifikasi Aset</a></li>
                                        <li><a href="{{ route('master-data.kode-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.kode-barang.*'])) }}">Kode Barang</a></li>
                                        <li><a href="{{ route('master-data.kategori-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.kategori-barang.*'])) }}">Kategori Barang</a></li>
                                        <li><a href="{{ route('master-data.jenis-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.jenis-barang.*'])) }}">Jenis Barang</a></li>
                                        <li><a href="{{ route('master-data.subjenis-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.subjenis-barang.*'])) }}">Subjenis Barang</a></li>          
                                        <li><a href="{{ route('master-data.data-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.data-barang.*'])) }}">Data Barang</a></li>
                                        <li><a href="{{ route('master-data.import-struktur-barang.index') }}" class="{{ $linkClass($isRoute(['master-data.import-struktur-barang.*'])) }}">Import Struktur Barang</a></li>

                                    <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Data Inventory</li>
                                        <li><a href="{{ route('inventory.data-inventory.index') }}" class="{{ $linkClass($isRoute(['inventory.data-inventory.*'])) }}">Data Inventory</a></li>
                                        <li><a href="{{ route('inventory.scan-qr') }}" class="{{ $linkClass($isRoute(['inventory.scan-qr'])) }}">Scan QR Code</a></li>

                                    <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Stok &amp; Transaksi</li>
                                        <li><a href="{{ route('inventory.data-stock.index') }}" class="{{ $linkClass($isRoute(['inventory.data-stock.*'])) }}">Data Stock</a></li>
                                        <li><a href="{{ route('reports.kartu-stok') }}" class="{{ $linkClass($isRoute(['reports.kartu-stok'])) }}">Kartu Stok</a></li>
                                        <li><a href="{{ route('inventory.stock-adjustment.index') }}" class="{{ $linkClass($isRoute(['inventory.stock-adjustment.*'])) }}">Stock Adjustment / Opname</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessAsset)
                            @php($assetOpen = $isRoute(['asset.*']))
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
                            @php($maintOpen = $isRoute(['maintenance.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('maintenance')">
                                    <span>Pemeliharaan</span>
                                    <svg id="maintenance-arrow" class="w-4 h-4 ml-auto transition-transform {{ $maintOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="maintenance-submenu" class="{{ $groupClass($maintOpen) }}">
                                    <li><a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="{{ $linkClass($isRoute(['maintenance.jadwal-maintenance.*'])) }}">Jadwal Pemeliharaan</a></li>
                                    <li><a href="{{ route('maintenance.kalibrasi-aset.index') }}" class="{{ $linkClass($isRoute(['maintenance.kalibrasi-aset.*'])) }}">Kalibrasi</a></li>
                                    <li><a href="{{ route('maintenance.service-report.index') }}" class="{{ $linkClass($isRoute(['maintenance.service-report.*'])) }}">Laporan Servis</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessFinance)
                            @php($financeOpen = $isRoute(['finance.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('finance')">
                                    <span>Keuangan</span>
                                    <svg id="finance-arrow" class="w-4 h-4 ml-auto transition-transform {{ $financeOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="finance-submenu" class="{{ $groupClass($financeOpen) }}">
                                    <li><a href="{{ route('finance.pembayaran.index') }}" class="{{ $linkClass($isRoute(['finance.pembayaran.*'])) }}">Pembayaran</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessReports)
                            @php($reportOpen = $isRoute(['reports.*','report.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('laporan')">
                                    <span>Laporan &amp; KPI</span>
                                    <svg id="laporan-arrow" class="w-4 h-4 ml-auto transition-transform {{ $reportOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="laporan-submenu" class="{{ $groupClass($reportOpen) }}">
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass($isRoute(['reports.index'])) }}">Ringkasan Laporan</a></li>
                                    <li><a href="{{ route('reports.stock-gudang') }}" class="{{ $linkClass($isRoute(['reports.stock-gudang', 'reports.stock-gudang.export'])) }}">Laporan Stok Gudang</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessMasterManajemen)
                            @php($masterOpen = $isRoute(['master-manajemen.*','master.unit-kerja.*','master.ruangan.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('master-management')">
                                    <span>Master Data</span>
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
                            @php($adminOpen = $isRoute(['admin.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('admin')">
                                    <span>Admin Panel</span>
                                    <svg id="admin-arrow" class="w-4 h-4 ml-auto transition-transform {{ $adminOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="admin-submenu" class="{{ $groupClass($adminOpen) }}">
                                    <li><a href="{{ route('admin.users.index') }}" class="{{ $linkClass($isRoute(['admin.users.*'])) }}">Manajemen User</a></li>
                                    <li><a href="{{ route('admin.roles.index') }}" class="{{ $linkClass($isRoute(['admin.roles.*'])) }}">Manajemen Role</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            <div id="global-loading-overlay" class="absolute inset-0 z-[9999] hidden items-center justify-center bg-gray-900/35">
                <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-xl">
                    <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" class="opacity-20" stroke="currentColor" stroke-width="4"></circle>
                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Memproses data, mohon tunggu...</span>
                </div>
            </div>
            <div id="global-loading-bar" class="absolute left-0 top-0 z-[9999] h-0.5 w-0 bg-blue-600 transition-all duration-300"></div>
            <!-- Header -->
            <header class="bg-white border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
                            <p class="text-xs text-gray-500 mt-0.5">SISTEM MANAJEMEN &amp; INVENTORY TERINTEGRASI</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <button class="relative p-2 text-gray-600 hover:text-gray-900">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                            </button>
                            <button class="relative p-2 text-gray-600 hover:text-gray-900">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="absolute top-0 right-0 block h-5 w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center ring-2 ring-white">3</span>
                            </button>
                            <!-- User Menu -->
                            <div class="relative">
                                <button 
                                    type="button" 
                                    id="user-menu-button"
                                    onclick="toggleUserMenu()"
                                    class="flex items-center space-x-3 text-left focus:outline-none"
                                >
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser?->name ?? 'AD') }}&background=1e40af&color=fff&size=128" alt="User" class="h-10 w-10 rounded-full">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $currentUser?->name ?? 'User' }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if($userPrimaryRole)
                                                {{ $userPrimaryRole->display_name }}
                                            @else
                                                User
                                            @endif
                                        </p>
                                    </div>
                                    <svg id="user-menu-arrow" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div 
                                    id="user-dropdown-menu"
                                    class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
                                >
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Profil
                                        </div>
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Pengaturan
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
            <main class="flex-1 overflow-y-auto bg-gray-100">
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
    
    <!-- jQuery + Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- Script layout dipindah ke resources/js/layout/app-layout.js --}}
</body>
</html>
