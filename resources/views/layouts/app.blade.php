<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }} - Sistem Informasi Manajemen Terintegrasi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Choices.js for searchable select dropdowns -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
</head>
<body class="font-sans antialiased bg-gray-100">
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

                        @if($canAccessPermintaan)
                            @php($permintaanOpen = $isRoute(['transaction.permintaan-barang.*', 'maintenance.permintaan-pemeliharaan.*', 'planning.rku.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('permintaan-unit')">
                                    <span>Permintaan Unit</span>
                                    <svg id="permintaan-unit-arrow" class="w-4 h-4 ml-auto transition-transform {{ $permintaanOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="permintaan-unit-submenu" class="{{ $groupClass($permintaanOpen) }}">
                                    <li><a href="{{ route('transaction.permintaan-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.permintaan-barang.*'])) }}">Permintaan Barang</a></li>
                                    <li><a href="{{ route('maintenance.permintaan-pemeliharaan.index') }}" class="{{ $linkClass($isRoute(['maintenance.permintaan-pemeliharaan.*'])) }}">Permintaan Pemeliharaan</a></li>
                                    <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass($isRoute(['planning.rku.*'])) }}">Permintaan RKU</a></li>
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
                                    <li><a href="{{ route('transaction.approval.index') }}" class="{{ $linkClass($isRoute(['transaction.approval.index','transaction.approval.show'])) }}">Daftar Approval</a></li>
                                    <li><a href="{{ route('transaction.approval.diagram') }}" class="{{ $linkClass($isRoute(['transaction.approval.diagram'])) }}">Riwayat Approval</a></li>
                                </ul>
                            </li>
                        @endif

                        @if($canAccessPlanning || $canAccessMasterManajemen)
                            @php($planningOpen = $isRoute(['planning.*','master.program.*','master.kegiatan.*','master.sub-kegiatan.*']))
                            <li>
                                <div class="flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('planning')">
                                    <span>Perencanaan</span>
                                    <svg id="planning-arrow" class="w-4 h-4 ml-auto transition-transform {{ $planningOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <ul id="planning-submenu" class="{{ $groupClass($planningOpen) }}">
                                    <li><a href="{{ route('master.program.index') }}" class="{{ $linkClass($isRoute(['master.program.*'])) }}">Program</a></li>
                                    <li><a href="{{ route('master.kegiatan.index') }}" class="{{ $linkClass($isRoute(['master.kegiatan.*'])) }}">Kegiatan</a></li>
                                    <li><a href="{{ route('master.sub-kegiatan.index') }}" class="{{ $linkClass($isRoute(['master.sub-kegiatan.*'])) }}">Sub Kegiatan</a></li>
                                    <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass($isRoute(['planning.rku.*'])) }}">Aktivitas</a></li>
                                    <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass(false) }}">Daftar RKU</a></li>
                                    <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass(false) }}">Status RKU</a></li>
                                    <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass(false) }}">Rincian Aktivitas</a></li>
                                    <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass(false) }}">Komponen Aktivitas</a></li>
                                    <li><a href="{{ route('planning.rekap-tahunan') }}" class="{{ $linkClass($isRoute(['planning.rekap-tahunan'])) }}">Rekap Tahunan</a></li>
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
                                    <li><a href="{{ route('procurement.proses-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.proses-pengadaan.*'])) }}">Proses Pengadaan</a></li>
                                    <li><a href="{{ route('procurement.proses-pengadaan.index') }}" class="{{ $linkClass(false) }}">Realisasi Pengadaan</a></li>
                                    <li><a href="{{ route('procurement.paket-pengadaan.index') }}" class="{{ $linkClass(false) }}">Rekap Pengadaan</a></li>
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
                                    <li><a href="{{ route('transaction.draft-distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.draft-distribusi.*'])) }}">Daftar Permintaan</a></li>
                                    <li><a href="{{ route('transaction.distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.distribusi.*'])) }}">Distribusi Barang</a></li>
                                    <li><a href="{{ route('transaction.penerimaan-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.penerimaan-barang.*'])) }}">Penerimaan Barang</a></li>
                                    <li><a href="{{ route('transaction.retur-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.retur-barang.*'])) }}">Retur Barang</a></li>
                                    <li><a href="{{ route('transaction.pemakaian-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.pemakaian-barang.*'])) }}">Pemakaian Barang</a></li>
                                    <li><a href="{{ route('transaction.compile-distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.compile-distribusi.*'])) }}">SBBK</a></li>
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

                                    <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Ringkasan</li>
                                        <li><a href="{{ route('inventory.data-inventory.index') }}" class="{{ $linkClass($isRoute(['inventory.data-inventory.*'])) }}">Ringkasan Inventory</a></li>

                                    <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Stok &amp; Transaksi</li>
                                        <li><a href="{{ route('inventory.scan-qr') }}" class="{{ $linkClass($isRoute(['inventory.scan-qr'])) }}">Scan QR Code</a></li>
                                        <li><a href="{{ route('inventory.data-stock.index') }}" class="{{ $linkClass($isRoute(['inventory.data-stock.*'])) }}">Data Stock</a></li>
                                        <li><a href="{{ route('reports.stock-gudang') }}" class="{{ $linkClass($isRoute(['reports.stock-gudang'])) }}">Kartu Stok</a></li>
                                        <li><a href="{{ route('inventory.stock-adjustment.index') }}" class="{{ $linkClass($isRoute(['inventory.stock-adjustment.*'])) }}">Stock Adjustment</a></li>
                                        <li><a href="{{ route('inventory.stock-adjustment.index') }}" class="{{ $linkClass(false) }}">Stock Opname</a></li>
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
                                    <li><a href="{{ route('asset.register-aset.index') }}" class="{{ $linkClass($isRoute(['asset.register-aset.*'])) }}">Register Aset</a></li>
                                    <li><a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="{{ $linkClass($isRoute(['asset.kartu-inventaris-ruangan.*'])) }}">KIR Aset</a></li>
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
                                    <li><a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="{{ $linkClass($isRoute(['maintenance.jadwal-maintenance.*'])) }}">Jadwal Maintenance</a></li>
                                    <li><a href="{{ route('maintenance.kalibrasi-aset.index') }}" class="{{ $linkClass($isRoute(['maintenance.kalibrasi-aset.*'])) }}">Kalibrasi</a></li>
                                    <li><a href="{{ route('maintenance.service-report.index') }}" class="{{ $linkClass($isRoute(['maintenance.service-report.*'])) }}">Service Report</a></li>
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
                                    <li><a href="{{ route('finance.pembayaran.index') }}" class="{{ $linkClass(false) }}">Realisasi Pembayaran</a></li>
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
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass($isRoute(['reports.index'])) }}">Laporan Perencanaan</a></li>
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass(false) }}">Laporan Pengadaan</a></li>
                                    <li><a href="{{ route('reports.stock-gudang') }}" class="{{ $linkClass($isRoute(['reports.stock-gudang'])) }}">Laporan Inventory</a></li>
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass(false) }}">Laporan Keuangan</a></li>
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass(false) }}">Laporan Aset</a></li>
                                    <li><a href="{{ route('reports.index') }}" class="{{ $linkClass(false) }}">Laporan Pemeliharaan</a></li>
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
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Sistem Manajemen Aset &amp; Inventory · single dashboard berbasis peran</p>
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
    
    <!-- Choices.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
    <script>
        // Verifikasi Choices.js ter-load
        if (typeof Choices !== 'undefined') {
            console.log('Choices.js loaded successfully');
            window.choicesLoaded = true;
        } else {
            console.warn('Choices.js not loaded, trying fallback...');
            // Fallback ke CDN alternatif
            var fallbackScript = document.createElement('script');
            fallbackScript.src = 'https://unpkg.com/choices.js@10.2.0/public/assets/scripts/choices.min.js';
            fallbackScript.onload = function() {
                window.choicesLoaded = true;
                console.log('Choices.js loaded from fallback CDN');
            };
            fallbackScript.onerror = function() {
                console.error('Choices.js failed to load from both CDNs');
            };
            document.head.appendChild(fallbackScript);
        }
    </script>
    
    <script>
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id + '-submenu');
            const arrow = document.getElementById(id + '-arrow');
            submenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-90');
        }

        // Toggle user dropdown menu
        function toggleUserMenu() {
            const menu = document.getElementById('user-dropdown-menu');
            const arrow = document.getElementById('user-menu-arrow');
            menu.classList.toggle('hidden');
            if (arrow) {
                arrow.classList.toggle('rotate-180');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-dropdown-menu');
            const userButton = document.getElementById('user-menu-button');
            
            if (userMenu && userButton && !userMenu.contains(event.target) && !userButton.contains(event.target)) {
                userMenu.classList.add('hidden');
                const arrow = document.getElementById('user-menu-arrow');
                if (arrow) {
                    arrow.classList.remove('rotate-180');
                }
            }
        });

        // Helper function untuk menginisialisasi Choices.js pada select element
        function initChoicesForSelect(selectElement, minOptions = 2) {
            if (!selectElement || selectElement.tagName !== 'SELECT') {
                return null;
            }

            // Cek apakah Choices.js sudah ter-load
            if (typeof Choices === 'undefined') {
                console.warn('Choices.js belum ter-load. Pastikan script Choices.js sudah di-include.');
                return null;
            }

            // Jika sudah diinisialisasi, destroy dulu
            if (selectElement.choicesInstance) {
                try {
                    selectElement.choicesInstance.destroy();
                } catch (e) {
                    // Ignore error jika sudah destroyed
                }
                selectElement.choicesInstance = null;
            }

            // Hitung jumlah opsi yang terlihat (exclude empty option)
            // Setelah filter, opsi yang tidak terlihat sudah dihapus dari DOM
            // Jadi semua opsi yang masih ada di DOM adalah opsi yang terlihat
            const visibleOptions = Array.from(selectElement.options).filter(opt => {
                return opt.value !== ''; // Exclude placeholder
            });
            const optionCount = visibleOptions.length;
            
            // Log untuk debugging
            if (selectElement.classList.contains('select-data-barang') || selectElement.classList.contains('select-satuan')) {
                console.log('Counting options for', selectElement.className, '- Total in DOM:', selectElement.options.length, 'Visible (non-empty):', optionCount);
            }
            
            // Untuk select-data-barang dan select-satuan, selalu inisialisasi jika ada minimal 1 opsi
            const isDataBarangOrSatuan = selectElement.classList.contains('select-data-barang') || 
                                         selectElement.classList.contains('select-satuan') ||
                                         selectElement.id === 'id_data_barang' ||
                                         selectElement.id === 'id_satuan';
            
            // Jika memiliki lebih dari minOptions opsi, atau jika select-data-barang/select-satuan dengan minimal 1 opsi
            if (optionCount > minOptions || (isDataBarangOrSatuan && optionCount > 0)) {
                try {
                    console.log('Initializing Choices.js for:', selectElement.id || selectElement.className || 'unnamed select', 'with', optionCount, 'visible options');
                    
                    // Untuk select-data-barang, pastikan hanya opsi yang terlihat yang digunakan
                    // Setelah filter, opsi yang tidak terlihat sudah dihapus dari DOM
                    // Jadi Choices.js akan membaca semua opsi yang masih ada di DOM (yang sudah ter-filter)
                    if (selectElement.classList.contains('select-data-barang')) {
                        console.log('Before Choices.js init for select-data-barang:', {
                            totalOptions: selectElement.options.length,
                            optionsInDOM: Array.from(selectElement.options).map(opt => ({
                                value: opt.value,
                                text: opt.textContent.substring(0, 30)
                            })).slice(0, 5)
                        });
                    }
                    
                    // Pastikan select element masih valid dan memiliki opsi sebelum initialize Choices.js
                    if (!selectElement || selectElement.tagName !== 'SELECT' || selectElement.options.length === 0) {
                        console.warn('Skipping Choices.js initialization: invalid select element or no options');
                        return;
                    }
                    
                    const choicesInstance = new Choices(selectElement, {
                        searchEnabled: true,
                        searchChoices: true,
                        itemSelectText: '',
                        placeholder: true,
                        placeholderValue: selectElement.querySelector('option[value=""]')?.textContent || 'Pilih...',
                        searchPlaceholderValue: 'Ketik minimal 2 karakter untuk mencari...',
                        shouldSort: true,
                        fuseOptions: {
                            threshold: 0.3,
                            distance: 100
                        },
                        shouldSortItems: true,
                        removeItemButton: false,
                        allowHTML: false // Suppress deprecation warning
                    });
                    
                    // Simpan instance ke select element untuk referensi
                    selectElement.choicesInstance = choicesInstance;
                    
                    // Setelah filter, opsi yang tidak terlihat sudah dihapus dari DOM
                    // Jadi Choices.js akan membaca semua opsi yang masih ada di DOM (yang sudah ter-filter)
                    // Tidak perlu filter lagi karena opsi yang tidak terlihat sudah dihapus
                    console.log('Choices.js initialized successfully with', optionCount, 'options');
                    
                    // Custom search filter: hanya tampilkan hasil jika input >= 2 karakter
                    setTimeout(function() {
                        const searchInput = choicesInstance.input.element;
                        const containerOuter = choicesInstance.containerOuter.element;
                        
                        if (searchInput && containerOuter) {
                            let lastSearchValue = '';
                            
                            // Flag untuk track apakah user sudah mulai mengetik
                            let hasUserTyped = false;
                            
                            // Intercept input events - hanya aktif setelah user mulai mengetik
                            searchInput.addEventListener('input', function(e) {
                                const searchValue = e.target.value.trim();
                                lastSearchValue = searchValue;
                                hasUserTyped = true; // Set flag bahwa user sudah mulai mengetik
                                
                                // Delay untuk memungkinkan Choices.js memproses dulu
                                setTimeout(function() {
                                    const dropdown = containerOuter.querySelector('.choices__list--dropdown');
                                    if (!dropdown) return;
                                    
                                    // Jika kurang dari 2 karakter DAN user sudah mulai mengetik
                                    if (searchValue.length > 0 && searchValue.length < 2) {
                                        // Sembunyikan semua item hasil pencarian
                                        const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                        items.forEach(item => {
                                            item.style.display = 'none';
                                        });
                                        
                                        // Tampilkan atau buat pesan "ketik minimal 2 karakter"
                                        let noResults = dropdown.querySelector('.choices__item--no-results');
                                        if (!noResults || !noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                            // Hapus no-results yang lama jika ada
                                            if (noResults) {
                                                noResults.remove();
                                            }
                                            
                                            // Buat pesan baru
                                            noResults = document.createElement('div');
                                            noResults.className = 'choices__item choices__item--no-results';
                                            noResults.setAttribute('data-select-text', 'Tekan untuk memilih');
                                            noResults.setAttribute('data-choice', '');
                                            noResults.setAttribute('data-choice-selectable', '');
                                            noResults.textContent = 'Ketik minimal 2 karakter untuk mencari...';
                                            dropdown.appendChild(noResults);
                                        }
                                        
                                        // Pastikan dropdown terlihat
                                        dropdown.classList.remove('is-hidden');
                                    } else if (searchValue.length >= 2) {
                                        // Jika >= 2 karakter, sembunyikan pesan "ketik minimal 2 karakter"
                                        const noResults = dropdown.querySelector('.choices__item--no-results');
                                        if (noResults && noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                            noResults.remove();
                                        }
                                        
                                        // Tampilkan semua item hasil pencarian
                                        const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                        items.forEach(item => {
                                            item.style.display = '';
                                        });
                                    } else if (searchValue.length === 0) {
                                        // Jika input dikosongkan, reset flag dan tampilkan semua opsi
                                        hasUserTyped = false;
                                        const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                        items.forEach(item => {
                                            item.style.display = '';
                                        });
                                        
                                        // Hapus pesan jika ada
                                        const noResults = dropdown.querySelector('.choices__item--no-results');
                                        if (noResults && noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                            noResults.remove();
                                        }
                                    }
                                }, 10);
                            });
                            
                            // Reset flag saat input dikosongkan atau blur
                            searchInput.addEventListener('blur', function() {
                                setTimeout(function() {
                                    if (this.value.trim().length === 0) {
                                        hasUserTyped = false;
                                    }
                                }.bind(this), 100);
                            });
                            
                            // Update placeholder
                            searchInput.placeholder = 'Ketik minimal 2 karakter untuk mencari...';
                            
                            // Handle saat dropdown dibuka pertama kali - pastikan semua opsi ditampilkan
                            const containerInner = containerOuter.querySelector('.choices__inner');
                            
                            if (containerInner) {
                                containerInner.addEventListener('click', function() {
                                    setTimeout(function() {
                                        const dropdown = containerOuter.querySelector('.choices__list--dropdown');
                                        if (!dropdown) return;
                                        
                                        const searchValue = searchInput.value.trim();
                                        
                                        // Jika belum ada input atau input kosong, tampilkan semua opsi
                                        if (searchValue.length === 0) {
                                            const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                            items.forEach(item => {
                                                item.style.display = '';
                                            });
                                            
                                            // Hapus pesan "ketik minimal 2 karakter" jika ada
                                            const noResults = dropdown.querySelector('.choices__item--no-results');
                                            if (noResults && noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                                noResults.remove();
                                            }
                                        }
                                    }, 100);
                                });
                            }
                            
                            // Juga handle saat search input di-focus - tampilkan semua opsi jika kosong
                            searchInput.addEventListener('focus', function() {
                                setTimeout(function() {
                                    const dropdown = containerOuter.querySelector('.choices__list--dropdown');
                                    if (!dropdown) return;
                                    
                                    const searchValue = this.value.trim();
                                    
                                    // Jika belum ada input, tampilkan semua opsi
                                    if (searchValue.length === 0) {
                                        const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                        items.forEach(item => {
                                            item.style.display = '';
                                        });
                                        
                                        // Hapus pesan "ketik minimal 2 karakter" jika ada
                                        const noResults = dropdown.querySelector('.choices__item--no-results');
                                        if (noResults && noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                            noResults.remove();
                                        }
                                    }
                                }.bind(this), 100);
                            });
                        }
                    }, 150);
                    
                    // Simpan instance untuk referensi
                    selectElement.choicesInstance = choicesInstance;
                    console.log('Choices.js initialized successfully for:', selectElement.id || 'unnamed select');
                    return choicesInstance;
                } catch (error) {
                    console.error('Error initializing Choices.js for select:', selectElement.id || 'unnamed select', error);
                    return null;
                }
            } else {
                console.log('Skipping Choices.js for:', selectElement.id || 'unnamed select', '- only', optionCount, 'options (min:', minOptions + 1, ')');
            }
            return null;
        }

        // Expose function globally untuk digunakan di halaman lain
        window.initChoicesForSelect = initChoicesForSelect;

        // Function untuk initialize semua searchable selects
        function initializeSearchableSelects() {
            // Cek apakah Choices.js sudah ter-load
            if (typeof Choices === 'undefined') {
                console.warn('Choices.js belum ter-load. Menunggu...');
                // Coba lagi setelah 100ms, maksimal 10 kali (1 detik)
                if (!window.choicesRetryCount) window.choicesRetryCount = 0;
                if (window.choicesRetryCount < 10) {
                    window.choicesRetryCount++;
                    setTimeout(initializeSearchableSelects, 100);
                } else {
                    console.error('Choices.js gagal ter-load setelah beberapa kali percobaan.');
                }
                return;
            }

            // Reset retry counter jika berhasil
            window.choicesRetryCount = 0;

            // Field-field yang biasanya memiliki banyak data dan perlu searchable
            const searchableFieldIds = [
                // Inventory & Transaction
                'id_data_barang',           // Data Barang
                'id_item',                  // Inventory Item
                
                // Master Data - Form dropdowns
                'id_subjenis_barang',       // Subjenis Barang (Data Barang create/edit)
                'id_satuan',                // Satuan (Data Barang create/edit)
                'id_kategori_barang',       // Kategori Barang (Jenis Barang create/edit)
                'id_jenis_barang',          // Jenis Barang (Subjenis Barang create/edit)
                'id_kode_barang',           // Kode Barang (Kategori Barang create/edit)
                'id_aset',                  // Aset (Kode Barang create/edit)
                
                // Master Manajemen
                'id_unit_kerja',            // Unit Kerja
                'id_ruangan',               // Ruangan
                'id_pegawai',               // Pegawai
                'id_penanggung_jawab',      // Penanggung Jawab
                // id_gudang dihapus - tidak perlu searchable
                
                // Planning & Budget
                // id_anggaran dihapus - tidak perlu searchable
                'id_sub_kegiatan',          // Sub Kegiatan
                'id_program',               // Program
                'id_kegiatan',              // Kegiatan
            ];

            let initializedCount = 0;

            // Initialize Choices.js untuk field-field yang memiliki banyak opsi
            searchableFieldIds.forEach(function(fieldId) {
                const selectElement = document.getElementById(fieldId);
                if (selectElement) {
                    // Tentukan threshold berdasarkan field
                    let minOpts = 2; // Default minimal 3 opsi
                    
                    // Field-field yang biasanya memiliki banyak data, gunakan threshold lebih rendah
                    const lowThresholdFields = [
                        'id_data_barang',      // Data Barang
                        'id_subjenis_barang',  // Subjenis Barang
                        'id_satuan',           // Satuan
                        'id_kategori_barang',  // Kategori Barang
                        'id_jenis_barang',     // Jenis Barang
                        'id_kode_barang',      // Kode Barang
                        'id_aset',             // Aset
                        'id_unit_kerja',       // Unit Kerja
                        'id_ruangan',          // Ruangan
                        // id_gudang dihapus - tidak perlu searchable
                    ];
                    
                    if (lowThresholdFields.includes(fieldId)) {
                        minOpts = 1; // Minimal 2 opsi untuk field-field ini
                    }
                    
                    const result = initChoicesForSelect(selectElement, minOpts);
                    if (result) {
                        initializedCount++;
                        console.log('Choices.js initialized for:', fieldId);
                    }
                } else {
                    console.log('Select element not found:', fieldId);
                }
            });

            // Juga inisialisasi untuk select dengan class tertentu atau data attribute
            document.querySelectorAll('select[data-searchable="true"], select.select-searchable, select.select-data-barang, select.select-satuan').forEach(function(select) {
                // Skip jika sudah diinisialisasi
                if (select.choicesInstance) {
                    console.log('Skipping already initialized select:', select.className);
                    return;
                }
                
                // Pastikan semua option memiliki textContent yang benar (khusus untuk select-satuan)
                if (select.classList.contains('select-satuan')) {
                    Array.from(select.options).forEach(function(option) {
                        if (!option.textContent || option.textContent.trim() === '') {
                            option.textContent = option.innerText || option.getAttribute('label') || option.value;
                        }
                        const text = option.textContent.trim();
                        if (text && text.length > 0) {
                            option.textContent = text;
                        }
                    });
                }
                
                // Untuk select-data-barang dan select-satuan, selalu inisialisasi (threshold 0)
                let minOpts = 2;
                if (select.classList.contains('select-data-barang') || select.classList.contains('select-satuan')) {
                    minOpts = 0; // Selalu inisialisasi jika ada opsi
                }
                
                const optionCount = Array.from(select.options).filter(opt => opt.value !== '').length;
                console.log('Found select with class:', select.className, 'options:', optionCount, 'minOpts:', minOpts);
                
                const result = initChoicesForSelect(select, minOpts);
                if (result) {
                    initializedCount++;
                    console.log('Choices.js initialized for select with class:', select.className, 'options:', optionCount);
                } else {
                    console.log('Choices.js NOT initialized for select with class:', select.className, 'options:', optionCount, 'minOpts:', minOpts);
                }
            });

            if (initializedCount > 0) {
                console.log('Choices.js: Total', initializedCount, 'select(s) initialized');
            }
        }

        // Initialize Choices.js for searchable select dropdowns
        // Tunggu sampai Choices.js benar-benar ter-load dan DOM ready
        function waitForChoicesAndInit() {
            console.log('waitForChoicesAndInit called. Choices available:', typeof Choices !== 'undefined', 'choicesLoaded:', window.choicesLoaded);
            
            // Cek apakah Choices.js sudah ter-load
            if (typeof Choices !== 'undefined') {
                console.log('Choices.js is available, initializing...');
                // Tunggu DOM ready jika belum
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('DOMContentLoaded fired, initializing Choices.js...');
                        setTimeout(initializeSearchableSelects, 100);
                    });
                } else {
                    // DOM sudah ready, langsung initialize
                    console.log('DOM already ready, initializing Choices.js...');
                    setTimeout(initializeSearchableSelects, 100);
                }
            } else {
                // Choices.js belum ter-load, coba lagi setelah 100ms (maksimal 5 detik)
                if (!window.choicesRetryCount) window.choicesRetryCount = 0;
                if (window.choicesRetryCount < 50) {
                    window.choicesRetryCount++;
                    setTimeout(waitForChoicesAndInit, 100);
                } else {
                    console.error('Choices.js gagal ter-load setelah 5 detik');
                }
            }
        }
        
        // Mulai proses setelah semua script selesai
        console.log('Setting up Choices.js initialization...');
        if (document.readyState === 'complete') {
            console.log('Document already complete, starting initialization...');
            waitForChoicesAndInit();
        } else {
            // Tunggu window load untuk memastikan semua resource ter-load
            window.addEventListener('load', function() {
                console.log('Window load event fired, starting initialization...');
                setTimeout(function() {
                    waitForChoicesAndInit();
                }, 200);
            });
            
            // Juga coba saat DOMContentLoaded sebagai fallback
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('DOMContentLoaded event fired, starting initialization...');
                    setTimeout(waitForChoicesAndInit, 200);
                });
            } else {
                // DOM sudah interactive, langsung coba
                setTimeout(waitForChoicesAndInit, 200);
            }
        }
        
        // Final initialization setelah semua script selesai
        // Ini akan memastikan Choices.js ter-initialize bahkan jika ada script lain yang delay
        setTimeout(function() {
            console.log('Final initialization attempt...');
            if (typeof Choices !== 'undefined' && typeof initializeSearchableSelects === 'function') {
                console.log('Choices.js available in final check, initializing...');
                initializeSearchableSelects();
            } else {
                console.warn('Choices.js not available in final check. Choices:', typeof Choices, 'initializeSearchableSelects:', typeof initializeSearchableSelects);
            }
        }, 1000);
    </script>
</body>
</html>
