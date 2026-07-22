<?php
use App\Helpers\PermissionHelper;

$currentUser = $currentUser ?? auth()->user();
$isRoute = fn (array $patterns) => request()->routeIs(...$patterns);
$linkClass = fn (bool $active = false) => 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-150 '.($active ? 'bg-blue-600 text-white border-l-4 border-white shadow-md' : 'text-blue-200 hover:bg-blue-800');
$groupClass = fn (bool $open = false) => 'pl-4 mt-2 space-y-1 '.($open ? '' : 'hidden');

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

$showPlanningSidebar = $currentUser
    && ($canAccessPlanning || $canAccessMasterManajemen)
    && (
        PermissionHelper::canAccess($currentUser, 'master.program.index')
        || PermissionHelper::canAccess($currentUser, 'master.kegiatan.index')
        || PermissionHelper::canAccess($currentUser, 'master.sub-kegiatan.index')
        || PermissionHelper::canAccess($currentUser, 'planning.rku.index')
        || PermissionHelper::canAccess($currentUser, 'planning.rekap-tahunan')
    );

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
?>

<!-- MAIN SECTION -->
<div class="mb-2">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        MAIN
    </div>
    <ul class="space-y-1">
        <li>
            <a href="{{ route('user.dashboard') }}" class="{{ $linkClass($isRoute(['user.dashboard'])) }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>
</div>

<!-- OPERASIONAL SECTION -->
@if($currentUser && ($canAccessPermintaan || $canAccessApproval || $canAccessPengurusBarang))
<div class="mb-2">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        OPERASIONAL
    </div>
    <ul class="space-y-1">
        @if($canAccessPermintaan || PermissionHelper::canAccess($currentUser, 'user.requests.index'))
            @php $transaksiOpen = $isRoute(['transaction.permintaan-barang.*', 'maintenance.permintaan-pemeliharaan.*', 'planning.rku.*', 'user.requests.*', 'transaction.peminjaman-barang.*', 'transaction.pengembalian-barang.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('transaksi')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="flex-1">Transaksi</span>
                    <svg class="w-4 h-4 transition-transform {{ $transaksiOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($transaksiOpen) }}">
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
                </ul>
            </li>
        @endif

        @if($canAccessApproval)
            @php $approvalOpen = $isRoute(['transaction.approval.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('approval')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="flex-1">Approval</span>
                    <svg class="w-4 h-4 transition-transform {{ $approvalOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($approvalOpen) }}">
                    <li><a href="{{ route('transaction.approval.index') }}" class="{{ $linkClass($isRoute(['transaction.approval.index','transaction.approval.show'])) }}">Riwayat Approval</a></li>
                </ul>
            </li>
        @endif

        @if($canAccessPengurusBarang)
            @php $distribusiOpen = $isRoute(['transaction.draft-distribusi.*','transaction.distribusi.*','transaction.penerimaan-barang.*','transaction.retur-barang.*','transaction.compile-distribusi.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('distribusi')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span class="flex-1">Distribusi</span>
                    <svg class="w-4 h-4 transition-transform {{ $distribusiOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($distribusiOpen) }}">
                    @if(PermissionHelper::canAccess($currentUser, 'transaction.draft-distribusi.index'))
                        <li><a href="{{ route('transaction.draft-distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.draft-distribusi.*'])) }}">Daftar Permintaan</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'transaction.distribusi.index'))
                        <li><a href="{{ route('transaction.distribusi.index') }}" class="{{ $linkClass($isRoute(['transaction.distribusi.*'])) }}">Distribusi (SBBK)</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'transaction.penerimaan-barang.index'))
                        <li><a href="{{ route('transaction.penerimaan-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.penerimaan-barang.*'])) }}">Penerimaan</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'transaction.retur-barang.index'))
                        <li><a href="{{ route('transaction.retur-barang.index') }}" class="{{ $linkClass($isRoute(['transaction.retur-barang.*'])) }}">Retur</a></li>
                    @endif
                </ul>
            </li>
        @endif
    </ul>
</div>
@endif

<!-- MANAJEMEN SECTION -->
@if($showInventorySidebarGroup || $canAccessAsset || $canAccessMaintenance || $canAccessFinance)
<div class="mb-2">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        MANAJEMEN
    </div>
    <ul class="space-y-1">
        @if($showInventorySidebarGroup)
            @php $inventoryOpen = $isRoute(['inventory.*','master-data.*','master.gudang.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('inventory')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="flex-1">Inventory</span>
                    <svg class="w-4 h-4 transition-transform {{ $inventoryOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($inventoryOpen) }}">
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

                    @if($canInvLink('master-data.data-inventory.index') || $canInvLink('inventory.data-stock.index'))
                        <li class="px-4 pt-3 text-[11px] uppercase tracking-wide text-blue-300">Data & Stok</li>
                    @endif
                    @if($canInvLink('inventory.data-inventory.index'))
                        <li><a href="{{ route('inventory.data-inventory.index') }}" class="{{ $linkClass($isRoute(['inventory.data-inventory.*'])) }}">Data Inventory</a></li>
                    @endif
                    @if($canInvLink('inventory.data-stock.index'))
                        <li><a href="{{ route('inventory.data-stock.index') }}" class="{{ $linkClass($isRoute(['inventory.data-stock.*'])) }}">Data Stock</a></li>
                    @endif

                    @if($canInvLink('inventory.stock-adjustment.index'))
                        <li><a href="{{ route('inventory.stock-adjustment.index') }}" class="{{ $linkClass($isRoute(['inventory.stock-adjustment.*'])) }}">Stock Opname</a></li>
                    @endif

                    @if($canInvLink('inventory.farmasi-kedaluwarsa.index'))
                        <li><a href="{{ route('inventory.farmasi-kedaluwarsa.index') }}" class="{{ $linkClass($isRoute(['inventory.farmasi-kedaluwarsa.*'])) }}">Kedaluwarsa</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($canAccessAsset)
            @php $assetOpen = $isRoute(['asset.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('aset')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span class="flex-1">Aset</span>
                    <svg class="w-4 h-4 transition-transform {{ $assetOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($assetOpen) }}">
                    <li><a href="{{ route('asset.register-aset.index') }}" class="{{ $linkClass($isRoute(['asset.register-aset.*'])) }}">Register Aset</a></li>
                    <li><a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="{{ $linkClass($isRoute(['asset.kartu-inventaris-ruangan.*'])) }}">Dokumen KIR</a></li>
                    <li><a href="{{ route('asset.mutasi-aset.index') }}" class="{{ $linkClass($isRoute(['asset.mutasi-aset.*'])) }}">Mutasi Aset</a></li>
                </ul>
            </li>
        @endif

        @if($canAccessMaintenance)
            @php $maintOpen = $isRoute(['maintenance.jadwal-maintenance.*', 'maintenance.kalibrasi-aset.*', 'maintenance.service-report.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('pemeliharaan')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                    <span class="flex-1">Pemeliharaan</span>
                    <svg class="w-4 h-4 transition-transform {{ $maintOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($maintOpen) }}">
                    @if(PermissionHelper::canAccess($currentUser, 'maintenance.jadwal-maintenance.index'))
                        <li><a href="{{ route('maintenance.jadwal-maintenance.index') }}" class="{{ $linkClass($isRoute(['maintenance.jadwal-maintenance.*'])) }}">Jadwal</a></li>
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

        @if($canAccessFinance)
            @php $financeOpen = $isRoute(['finance.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('keuangan')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="flex-1">Keuangan</span>
                    <svg class="w-4 h-4 transition-transform {{ $financeOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($financeOpen) }}">
                    <li><a href="{{ route('finance.pembayaran.index') }}" class="{{ $linkClass($isRoute(['finance.pembayaran.*'])) }}">Pembayaran</a></li>
                </ul>
            </li>
        @endif
    </ul>
</div>
@endif

<!-- PERENCANAAN SECTION -->
@if($showPlanningSidebar || $canAccessProcurement)
<div class="mb-2">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        PERENCANAAN
    </div>
    <ul class="space-y-1">
        @if($showPlanningSidebar)
            @php $planOpen = $isRoute(['planning.*','master.program.*','master.kegiatan.*','master.sub-kegiatan.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('perencanaan')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="flex-1">Perencanaan</span>
                    <svg class="w-4 h-4 transition-transform {{ $planOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($planOpen) }}">
                    @if(PermissionHelper::canAccess($currentUser, 'master.program.index'))
                        <li><a href="{{ route('master.program.index') }}" class="{{ $linkClass($isRoute(['master.program.*'])) }}">Program</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'master.kegiatan.index'))
                        <li><a href="{{ route('master.kegiatan.index') }}" class="{{ $linkClass($isRoute(['master.kegiatan.*'])) }}"> Kegiatan</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'master.sub-kegiatan.index'))
                        <li><a href="{{ route('master.sub-kegiatan.index') }}" class="{{ $linkClass($isRoute(['master.sub-kegiatan.*'])) }}">Sub Kegiatan</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'planning.rku.create'))
                        <li><a href="{{ route('planning.rku.create') }}" class="{{ $linkClass($isRoute(['planning.rku.create'])) }}">Input RKU</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'planning.rku.index'))
                        <li><a href="{{ route('planning.rku.index') }}" class="{{ $linkClass($isRoute(['planning.rku.index', 'planning.rku.show', 'planning.rku.edit'])) }}">Daftar RKU</a></li>
                    @endif
                    @if(PermissionHelper::canAccess($currentUser, 'planning.rekap-tahunan'))
                        <li><a href="{{ route('planning.rekap-tahunan') }}" class="{{ $linkClass($isRoute(['planning.rekap-tahunan'])) }}">Rekap Tahunan</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($canAccessProcurement)
            @php $procOpen = $isRoute(['procurement.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('pengadaan')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="flex-1">Pengadaan</span>
                    <svg class="w-4 h-4 transition-transform {{ $procOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($procOpen) }}">
                    <li><a href="{{ route('procurement.paket-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.paket-pengadaan.*'])) }}">Paket</a></li>
                    <li><a href="{{ route('procurement.proses-pengadaan.index') }}" class="{{ $linkClass($isRoute(['procurement.proses-pengadaan.*'])) }}">Proses Pengadaan</a></li>
                </ul>
            </li>
        @endif
    </ul>
</div>
@endif

<!-- MASTER DATA SECTION -->
@if($canAccessMasterManajemen || $canAccessMasterData)
<div class="mb-2">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
        MASTER DATA
    </div>
    <ul class="space-y-1">
        @if($canAccessMasterManajemen)
            @php $masterOpen = $isRoute(['master-manajemen.*','master.unit-kerja.*','master.ruangan.*']); @endphp
            <li>
                <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('master')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="flex-1">Organisasi</span>
                    <svg class="w-4 h-4 transition-transform {{ $masterOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <ul class="{{ $groupClass($masterOpen) }}">
                    <li><a href="{{ route('master-manajemen.master-pegawai.index') }}" class="{{ $linkClass($isRoute(['master-manajemen.master-pegawai.*'])) }}">Pegawai</a></li>
                    <li><a href="{{ route('master-manajemen.master-jabatan.index') }}" class="{{ $linkClass($isRoute(['master-manajemen.master-jabatan.*'])) }}">Jabatan</a></li>
                    <li><a href="{{ route('master.unit-kerja.index') }}" class="{{ $linkClass($isRoute(['master.unit-kerja.*'])) }}">Unit Kerja</a></li>
                    <li><a href="{{ route('master.ruangan.index') }}" class="{{ $linkClass($isRoute(['master.ruangan.*'])) }}">Ruangan</a></li>
                </ul>
            </li>
        @endif
    </ul>
</div>
@endif

<!-- MONITORING SECTION -->
@if($canAccessReports)
<div class="mb-2">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        MONITORING
    </div>
    <ul class="space-y-1">
        @php $reportOpen = $isRoute(['reports.*','report.*']); @endphp
        <li>
            <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('laporan')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="flex-1">Laporan</span>
                <svg class="w-4 h-4 transition-transform {{ $reportOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </div>
            <ul class="{{ $groupClass($reportOpen) }}">
                <li><a href="{{ route('reports.index') }}" class="{{ $linkClass($isRoute(['reports.index'])) }}">Ringkasan</a></li>
                <li><a href="{{ route('reports.stock-gudang') }}" class="{{ $linkClass($isRoute(['reports.stock-gudang', 'reports.stock-gudang.export'])) }}">Stok Gudang</a></li>
            </ul>
        </li>
    </ul>
</div>
@endif

<!-- SYSTEM ADMINISTRATION SECTION -->
@if($currentUser && PermissionHelper::canAccess($currentUser, 'admin.*'))
<div class="mb-2 mt-6">
    <div class="mb-2 flex items-center gap-2 px-4 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        SYSTEM ADMIN
    </div>
    <ul class="space-y-1">
        @php $adminOpen = $isRoute(['admin.*']); @endphp
        <li>
            <div class="flex items-center px-3 py-2 rounded-lg text-blue-200 hover:bg-blue-800 cursor-pointer" onclick="toggleSubmenu('admin')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="flex-1">Akses & Kontrol</span>
                <svg class="w-4 h-4 transition-transform {{ $adminOpen ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </div>
            <ul class="{{ $groupClass($adminOpen) }}">
                <li><a href="{{ route('admin.users.index') }}" class="{{ $linkClass($isRoute(['admin.users.*'])) }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    User & Account
                </a></li>
                <li><a href="{{ route('admin.roles.index') }}" class="{{ $linkClass($isRoute(['admin.roles.*'])) }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Role & Workflow
                </a></li>
                @if(config('sipeni.feature_print_templates') && PermissionHelper::canAccess($currentUser, 'admin.print-templates.index'))
                    <li><a href="{{ route('admin.print-templates.index') }}" class="{{ $linkClass($isRoute(['admin.print-templates.*'])) }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        Workflow Template
                    </a></li>
                @endif
                <li><a href="{{ route('admin.audit-trail.index') }}" class="{{ $linkClass($isRoute(['admin.audit-trail.*'])) }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Activity Timeline
                </a></li>
            </ul>
        </li>
    </ul>
</div>
@endif