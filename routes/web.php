<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Inventory\InventoryQrScanController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\AssetController;
use App\Http\Controllers\User\RequestController;
use App\Http\Controllers\Master\UnitKerjaController;
use App\Http\Controllers\Master\GudangController;
use App\Http\Controllers\Master\RuanganController;
use App\Http\Controllers\Master\ProgramController;
use App\Http\Controllers\Master\KegiatanController;
use App\Http\Controllers\Master\SubKegiatanController;
use App\Http\Controllers\Master\AsetController;
use App\Http\Controllers\Master\KodeBarangController;
use App\Http\Controllers\Master\KategoriBarangController;
use App\Http\Controllers\Master\JenisBarangController;
use App\Http\Controllers\Master\SubjenisBarangController;
use App\Http\Controllers\Master\DataBarangController;
use App\Http\Controllers\Master\StrukturBarangImportController;
use App\Http\Controllers\Master\SatuanController;
use App\Http\Controllers\Master\SumberAnggaranController;
use App\Http\Controllers\Inventory\DataStockController;
use App\Http\Controllers\Inventory\DataInventoryController;
use App\Http\Controllers\Transaction\DistribusiController;
use App\Http\Controllers\Transaction\PermintaanBarangController;
use App\Http\Controllers\Transaction\PenerimaanBarangController;
use App\Http\Controllers\Transaction\ReturBarangController;
use App\Http\Controllers\Transaction\PemakaianBarangController;
use App\Http\Controllers\Asset\RegisterAsetController;
use App\Http\Controllers\Planning\RkuController;
use App\Http\Controllers\Procurement\PaketPengadaanController;
use App\Http\Controllers\Procurement\ProsesPengadaanController;
use App\Http\Controllers\Finance\PembayaranController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Api\MasterLookupController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get'); // Fallback untuk GET request
Route::get('/scan/inventory-item', InventoryQrScanController::class)->name('inventory-item.scan');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('user.dashboard');
    
    // Portal unit (nama route = permission; middleware role memicu pengecekan permission lewat route name)
    Route::get('/assets', [AssetController::class, 'index'])->name('user.assets.index')->middleware(['role:admin,pegawai']);
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('user.assets.show')->middleware(['role:admin,pegawai']);
    
    Route::get('/requests', [RequestController::class, 'index'])->name('user.requests.index')->middleware(['role:admin,pegawai']);
    Route::get('/requests/create', [RequestController::class, 'create'])->name('user.requests.create')->middleware(['role:admin,pegawai']);
    Route::post('/requests', [RequestController::class, 'store'])->name('user.requests.store')->middleware(['role:admin,pegawai']);
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('user.requests.show')->middleware(['role:admin,pegawai']);

    // Lookup master (ruangan & pegawai per unit kerja) — untuk form yang ter-filter dinamis
    Route::get('api/master/gudang-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'gudangByUnit'])->name('api.master.gudang-by-unit');
    Route::get('api/master/ruangan-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'ruanganByUnit'])->name('api.master.ruangan-by-unit');
    Route::get('api/master/pegawai-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'pegawaiByUnit'])->name('api.master.pegawai-by-unit');
    
    // Master Manajemen - Admin only
    Route::prefix('master-manajemen')->name('master-manajemen.')->middleware(['role:admin'])->group(function () {
        Route::resource('master-pegawai', \App\Http\Controllers\MasterManajemen\MasterPegawaiController::class);
        Route::resource('master-jabatan', \App\Http\Controllers\MasterManajemen\MasterJabatanController::class);
    });
    
    Route::prefix('master')->name('master.')->middleware(['role:admin'])->group(function () {
        Route::resource('unit-kerja', UnitKerjaController::class);
        Route::resource('gudang', GudangController::class)->middleware(['role:admin,admin_gudang']);
        Route::resource('ruangan', RuanganController::class);
        Route::resource('program', ProgramController::class);
        Route::resource('kegiatan', KegiatanController::class);
        Route::resource('sub-kegiatan', SubKegiatanController::class);
    });
    
    // Master Data - Admin & Admin Gudang
    Route::prefix('master-data')->name('master-data.')->middleware(['role:admin,admin_gudang'])->group(function () {
        Route::get('import-struktur-barang', [StrukturBarangImportController::class, 'index'])->name('import-struktur-barang.index');
        Route::post('import-struktur-barang', [StrukturBarangImportController::class, 'import'])->name('import-struktur-barang.import');
        Route::get('import-struktur-barang/template/download', [StrukturBarangImportController::class, 'downloadTemplate'])->name('import-struktur-barang.template');

        Route::resource('aset', AsetController::class)->middleware(['role:admin']);
        Route::resource('kode-barang', KodeBarangController::class)->middleware(['role:admin']);
        Route::resource('kategori-barang', KategoriBarangController::class)->middleware(['role:admin']);
        Route::resource('jenis-barang', JenisBarangController::class)->middleware(['role:admin']);
        Route::resource('subjenis-barang', SubjenisBarangController::class)->middleware(['role:admin']);
        Route::resource('data-barang', DataBarangController::class);
        Route::resource('satuan', SatuanController::class)->middleware(['role:admin']);
        Route::resource('sumber-anggaran', SumberAnggaranController::class)->middleware(['role:admin']);
    });
    
    // Inventory - Admin, Admin Gudang (semua kategori), Kepala Unit, Admin Unit (Pegawai), Kasubbag TU
    Route::prefix('inventory')->name('inventory.')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,kepala_unit,pegawai,kasubbag_tu'])->group(function () {
        Route::get('data-stock', [DataStockController::class, 'index'])->name('data-stock.index');
        Route::get('scan-qr', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'scanQrPage'])->name('scan-qr');
        Route::resource('data-inventory', DataInventoryController::class);
        Route::get('inventory-item/{id}/template-qr', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'templateQr'])->name('inventory-item.template-qr');
        Route::get('inventory-item/{id}/template-qr/download', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'downloadTemplateQr'])->name('inventory-item.template-qr.download');
        Route::resource('inventory-item', \App\Http\Controllers\Inventory\InventoryItemController::class);
        
        // Stock Adjustment
        Route::resource('stock-adjustment', \App\Http\Controllers\Inventory\StockAdjustmentController::class);
        Route::post('stock-adjustment/{id}/approve', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'approve'])->name('stock-adjustment.approve');
        Route::post('stock-adjustment/{id}/reject', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'reject'])->name('stock-adjustment.reject');
        Route::post('stock-adjustment/{id}/ajukan', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'ajukan'])->name('stock-adjustment.ajukan');
    });
    
    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/gudang/{id}/ruangans', function ($id) {
            $gudang = \App\Models\MasterGudang::with('unitKerja')->findOrFail($id);
            $ruangans = \App\Models\MasterRuangan::where('id_unit_kerja', $gudang->id_unit_kerja)->get();
            return response()->json(['ruangans' => $ruangans]);
        });
        Route::get('/gudang/{id}/inventory', [\App\Http\Controllers\Transaction\DistribusiController::class, 'getInventoryByGudang'])->name('gudang.inventory');
        Route::get('/permintaan/{id}/detail', [\App\Http\Controllers\Transaction\DistribusiController::class, 'getPermintaanDetail'])->name('permintaan.detail');
        Route::get('/distribusi/{id}/detail', [\App\Http\Controllers\Transaction\PenerimaanBarangController::class, 'getDistribusiDetail'])->name('distribusi.detail');
        Route::get('/stock/{id}', function ($id) {
            $stock = \App\Models\DataStock::findOrFail($id);
            return response()->json([
                'qty_awal' => $stock->qty_awal,
                'qty_masuk' => $stock->qty_masuk,
                'qty_keluar' => $stock->qty_keluar,
                'qty_akhir' => $stock->qty_akhir,
            ]);
        })->name('stock.detail');
    });
    
    // Transaction
    Route::prefix('transaction')->name('transaction.')->group(function () {
        // Permintaan Barang - Pegawai, Kepala Unit, Kasubbag TU, Kepala Pusat, Admin
        Route::resource('permintaan-barang', PermintaanBarangController::class)->middleware(['role:admin,pegawai,kepala_unit,kasubbag_tu,kepala_pusat']);
        Route::post('permintaan-barang/{id}/ajukan', [PermintaanBarangController::class, 'ajukan'])->name('permintaan-barang.ajukan')->middleware(['role:admin,pegawai']);
        
        // Approval - Multi-level approval
        Route::prefix('approval')->name('approval.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'index'])->name('index')->middleware(['role:admin,kepala_unit,kasubbag_tu,kepala_pusat,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,perencanaan,pengadaan,keuangan']);
            Route::get('/{id}', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'show'])->name('show')->middleware(['role:admin,kepala_unit,kasubbag_tu,kepala_pusat,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,perencanaan,pengadaan,keuangan']);
            
            // Action khusus untuk Kepala Unit (mengetahui)
            Route::post('/{id}/mengetahui', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'mengetahui'])->name('mengetahui')->middleware(['role:admin,kepala_unit']);
            
            // Action khusus untuk Kasubbag TU (verifikasi/kembalikan)
            Route::post('/{id}/verifikasi', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'verifikasi'])->name('verifikasi')->middleware(['role:admin,kasubbag_tu']);
            Route::post('/{id}/kembalikan', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'kembalikan'])->name('kembalikan')->middleware(['role:admin,kasubbag_tu']);
            
            // Action untuk Kepala Pusat (approve/reject)
            Route::post('/{id}/approve', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'approve'])->name('approve')->middleware(['role:admin,kepala_pusat']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'reject'])->name('reject')->middleware(['role:admin,kepala_pusat']);
            
            // Action untuk disposisi - Admin Gudang/Pengurus Barang melakukan disposisi ke admin gudang kategori
            Route::post('/{id}/disposisi', [\App\Http\Controllers\Transaction\ApprovalPermintaanController::class, 'disposisi'])->name('disposisi')->middleware(['role:admin,admin_gudang']);
        });
        
        // Draft Distribusi - Admin Gudang Kategori memproses disposisi
        Route::prefix('draft-distribusi')->name('draft-distribusi.')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,kepala_unit,kepala_pusat,kasubbag_tu'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Transaction\DraftDistribusiController::class, 'index'])->name('index');
            Route::get('/create/{approvalLogId}', [\App\Http\Controllers\Transaction\DraftDistribusiController::class, 'create'])->name('create')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
            Route::post('/', [\App\Http\Controllers\Transaction\DraftDistribusiController::class, 'store'])->name('store')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
            Route::get('/{id}', [\App\Http\Controllers\Transaction\DraftDistribusiController::class, 'show'])->name('show');
        });
        
        // Compile Distribusi - Pengurus Barang/Admin Gudang compile menjadi SBBK
        Route::prefix('compile-distribusi')->name('compile-distribusi.')->middleware(['role:admin,admin_gudang'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Transaction\CompileDistribusiController::class, 'index'])->name('index');
            Route::get('/create/{permintaanId}', [\App\Http\Controllers\Transaction\CompileDistribusiController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Transaction\CompileDistribusiController::class, 'store'])->name('store');
        });
        
        // Distribusi - single SPPB flow
        Route::get('distribusi', [DistribusiController::class, 'index'])->name('distribusi.index')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,kepala_unit,kepala_pusat,kasubbag_tu']);
        Route::get('distribusi/create', [DistribusiController::class, 'create'])->name('distribusi.create')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::post('distribusi', [DistribusiController::class, 'store'])->name('distribusi.store')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::get('distribusi/{id}', [DistribusiController::class, 'show'])->name('distribusi.show')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,kepala_unit,kepala_pusat,kasubbag_tu']);
        Route::get('distribusi/{id}/edit', [DistribusiController::class, 'edit'])->name('distribusi.edit')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::put('distribusi/{id}', [DistribusiController::class, 'update'])->name('distribusi.update')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::delete('distribusi/{id}', [DistribusiController::class, 'destroy'])->name('distribusi.destroy')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::post('distribusi/{id}/proses', [DistribusiController::class, 'proses'])->name('distribusi.proses')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::post('distribusi/{id}/kirim', [DistribusiController::class, 'kirim'])->name('distribusi.kirim')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi']);
        Route::get('distribusi/api/gudang-tujuan/{permintaanId}', [DistribusiController::class, 'getGudangTujuanByPermintaan'])->name('distribusi.api.gudang-tujuan');
        
        // Penerimaan - Admin Gudang (semua kategori), Pegawai, Kepala Unit, Admin
        Route::resource('penerimaan-barang', PenerimaanBarangController::class)->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,pegawai,kepala_unit']);
        
        // Retur Barang - Admin Gudang (semua kategori), Pegawai, Kepala Unit, Admin
        // Route untuk return barang dari gudang unit ke gudang pusat
        Route::resource('retur-barang', ReturBarangController::class)->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,pegawai,kepala_unit']);
        Route::get('retur-barang/penerimaan/{id}/detail', [ReturBarangController::class, 'getPenerimaanDetail'])->name('retur-barang.penerimaan.detail')->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,pegawai,kepala_unit']);
        Route::post('retur-barang/{id}/terima', [ReturBarangController::class, 'terima'])->name('retur-barang.terima')->middleware(['role:admin,admin_gudang']);
        Route::post('retur-barang/{id}/tolak', [ReturBarangController::class, 'tolak'])->name('retur-barang.tolak')->middleware(['role:admin,admin_gudang']);
        Route::post('retur-barang/{id}/ajukan', [ReturBarangController::class, 'ajukan'])->name('retur-barang.ajukan')->middleware(['role:admin,admin_gudang,pegawai,kepala_unit']);
        
        // Pemakaian Barang - Admin Gudang (semua kategori), Pegawai, Kepala Unit, Admin
        Route::resource('pemakaian-barang', PemakaianBarangController::class)->middleware(['role:admin,admin_gudang,admin_gudang_aset,admin_gudang_persediaan,admin_gudang_farmasi,admin_gudang_unit,pegawai,kepala_unit']);
        Route::post('pemakaian-barang/{id}/ajukan', [PemakaianBarangController::class, 'ajukan'])->name('pemakaian-barang.ajukan')->middleware(['role:admin,admin_gudang,admin_gudang_unit,pegawai,kepala_unit']);
        Route::post('pemakaian-barang/{id}/approve', [PemakaianBarangController::class, 'approve'])->name('pemakaian-barang.approve')->middleware(['role:admin,kepala_unit']);
        Route::post('pemakaian-barang/{id}/reject', [PemakaianBarangController::class, 'reject'])->name('pemakaian-barang.reject')->middleware(['role:admin,kepala_unit']);
    });
    
    // Asset & KIR - Admin, Admin Gudang, Kepala Unit, Pegawai (untuk unit mereka sendiri)
    Route::prefix('asset')->name('asset.')->middleware(['role:admin,admin_gudang,kepala_unit,pegawai'])->group(function () {
        Route::resource('register-aset', RegisterAsetController::class);
        Route::get('register-aset/unit-kerja/{unit_kerja}', [RegisterAsetController::class, 'showUnitKerja'])->name('register-aset.unit-kerja.show');
        
        // Kartu Inventaris Ruangan (KIR)
        Route::get('kartu-inventaris-ruangan/unit/{id_unit_kerja}/dokumen', [\App\Http\Controllers\Asset\KartuInventarisRuanganController::class, 'dokumenUnitKerja'])->name('kartu-inventaris-ruangan.dokumen-unit');
        Route::resource('kartu-inventaris-ruangan', \App\Http\Controllers\Asset\KartuInventarisRuanganController::class);
        
        // Mutasi Aset
        Route::resource('mutasi-aset', \App\Http\Controllers\Asset\MutasiAsetController::class);
    });
    
    // Maintenance & Pemeliharaan - Admin, Admin Gudang, Kepala Unit, Pegawai
    Route::prefix('maintenance')->name('maintenance.')->middleware(['role:admin,admin_gudang,kepala_unit,pegawai'])->group(function () {
        // Permintaan Pemeliharaan
        Route::resource('permintaan-pemeliharaan', \App\Http\Controllers\Maintenance\PermintaanPemeliharaanController::class);
        Route::post('permintaan-pemeliharaan/{id}/ajukan', [\App\Http\Controllers\Maintenance\PermintaanPemeliharaanController::class, 'ajukan'])->name('permintaan-pemeliharaan.ajukan');
        
        // Jadwal Maintenance
        Route::resource('jadwal-maintenance', \App\Http\Controllers\Maintenance\JadwalMaintenanceController::class);
        
        // Kalibrasi Aset
        Route::resource('kalibrasi-aset', \App\Http\Controllers\Maintenance\KalibrasiAsetController::class);
        
        // Service Report
        Route::resource('service-report', \App\Http\Controllers\Maintenance\ServiceReportController::class);
    });
    
    // Planning - Admin only
    Route::prefix('planning')->name('planning.')->middleware(['role:admin'])->group(function () {
        Route::get('rekap-tahunan', [RkuController::class, 'rekapTahunan'])->name('rekap-tahunan');
        Route::resource('rku', RkuController::class);
    });
    
    // Procurement - Admin only
    Route::prefix('procurement')->name('procurement.')->middleware(['role:admin'])->group(function () {
        Route::get('proses-pengadaan', [ProsesPengadaanController::class, 'index'])->name('proses-pengadaan.index');
        Route::get('proses-pengadaan/{id}', [ProsesPengadaanController::class, 'show'])->name('proses-pengadaan.show');
        Route::resource('paket-pengadaan', PaketPengadaanController::class);
    });
    
    // Finance - Admin only
    Route::prefix('finance')->name('finance.')->middleware(['role:admin'])->group(function () {
        Route::resource('pembayaran', PembayaranController::class);
    });
    
    // Admin - Role & User Management
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });
    
    // Reports - Admin, Kepala Pusat, Admin Gudang, Kasubbag TU
    Route::prefix('reports')->name('reports.')->middleware(['role:admin,kepala_pusat,admin_gudang,kasubbag_tu'])->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('kartu-stok', [ReportController::class, 'kartuStok'])->name('kartu-stok');
        Route::get('stock-gudang', [ReportController::class, 'stockGudang'])->name('stock-gudang');
        Route::get('stock-gudang/export', [ReportController::class, 'exportStockGudang'])->name('stock-gudang.export');
        Route::get('transaksi-summary', [ReportController::class, 'transaksiSummary'])->name('transaksi-summary');
        Route::get('aset-summary', [ReportController::class, 'asetSummary'])->name('aset-summary');
    });
});
