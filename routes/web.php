<?php

use App\Http\Controllers\Admin\PrintTemplateController;
use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\ApiHelperController;
use App\Http\Controllers\Api\MasterLookupController;
use App\Http\Controllers\Asset\KartuInventarisRuanganController;
use App\Http\Controllers\Asset\MutasiAsetController;
use App\Http\Controllers\Asset\RegisterAsetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Finance\PembayaranController;
use App\Http\Controllers\Inventory\DataInventoryController;
use App\Http\Controllers\Inventory\DataStockController;
use App\Http\Controllers\Inventory\FarmasiKedaluwarsaController;
use App\Http\Controllers\Inventory\InventoryDataImportController;
use App\Http\Controllers\Inventory\InventoryItemController;
use App\Http\Controllers\Inventory\InventoryQrScanController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Maintenance\JadwalMaintenanceController;
use App\Http\Controllers\Maintenance\KalibrasiAsetController;
use App\Http\Controllers\Maintenance\PermintaanPemeliharaanController;
use App\Http\Controllers\Maintenance\ServiceReportController;
use App\Http\Controllers\Master\AsetController;
use App\Http\Controllers\Master\DataBarangController;
use App\Http\Controllers\Master\GudangController;
use App\Http\Controllers\Master\JenisBarangController;
use App\Http\Controllers\Master\KategoriBarangController;
use App\Http\Controllers\Master\KegiatanController;
use App\Http\Controllers\Master\KodeBarangController;
use App\Http\Controllers\Master\ProgramController;
use App\Http\Controllers\Master\RuanganController;
use App\Http\Controllers\Master\SatuanController;
use App\Http\Controllers\Master\StrukturBarangImportController;
use App\Http\Controllers\Master\SubjenisBarangController;
use App\Http\Controllers\Master\SubKegiatanController;
use App\Http\Controllers\Master\SumberAnggaranController;
use App\Http\Controllers\Master\UnitKerjaController;
use App\Http\Controllers\MasterManajemen\MasterJabatanController;
use App\Http\Controllers\MasterManajemen\MasterPegawaiController;
use App\Http\Controllers\Planning\RkuController;
use App\Http\Controllers\Procurement\PaketPengadaanController;
use App\Http\Controllers\Procurement\ProsesPengadaanController;
use App\Http\Controllers\Public\DocumentVerificationController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Transaction\ApprovalPermintaanController;
use App\Http\Controllers\Transaction\CompileDistribusiController;
use App\Http\Controllers\Transaction\DistribusiController;
use App\Http\Controllers\Transaction\DraftDistribusiController;
use App\Http\Controllers\Transaction\PemakaianBarangController;
use App\Http\Controllers\Transaction\PeminjamanBarangController;
use App\Http\Controllers\Transaction\PenerimaanBarangController;
use App\Http\Controllers\Transaction\PermintaanBarangController;
use App\Http\Controllers\Transaction\ReturBarangController;
use App\Services\PanduanPenggunaService;
use App\Http\Controllers\PanduanPenggunaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AssetController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\RequestController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware(
    app()->environment(['local', 'testing']) ? [] : 'throttle:5,1'
);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])->name('two-factor.verify');
Route::get('/scan/inventory-item', InventoryQrScanController::class)->name('inventory-item.scan');

Route::get('/verifikasi-dokumen/{token}', [DocumentVerificationController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('verifikasi-dokumen.show');

// Protected Routes (require authentication)
Route::middleware(['auth', 'scope.unit'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('user.dashboard');

    Route::get('/panduan', [PanduanPenggunaController::class, 'index'])->name('panduan.index');
    Route::get('/panduan/{doc}/pdf', [PanduanPenggunaController::class, 'pdf'])->name('panduan.pdf')->where('doc', '[a-z0-9_\-\.]+');
    Route::get('/panduan/{doc}', [PanduanPenggunaController::class, 'show'])->name('panduan.show')->where('doc', '[a-z0-9_\-\.]+');

    foreach (PanduanPenggunaService::legacyMarkdownRedirectPaths() as $legacyMarkdown) {
        Route::redirect('/'.$legacyMarkdown, '/panduan/'.PanduanPenggunaService::markdownFileToSlugMap()[$legacyMarkdown]);
    }

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/two-factor/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile.two-factor.confirm');
    Route::delete('/profile/two-factor', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/profile/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

    Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Portal unit (nama route = permission; middleware role memicu pengecekan permission lewat route name)
    Route::get('/assets', [AssetController::class, 'index'])->name('user.assets.index')->middleware(['role']);
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('user.assets.show')->middleware(['role']);

    Route::get('/requests', [RequestController::class, 'index'])->name('user.requests.index')->middleware(['role']);
    Route::get('/requests/create', [RequestController::class, 'create'])->name('user.requests.create')->middleware(['role']);
    Route::post('/requests', [RequestController::class, 'store'])->name('user.requests.store')->middleware(['role']);
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('user.requests.show')->middleware(['role']);

    // Lookup master (ruangan & pegawai per unit kerja) — untuk form yang ter-filter dinamis
    Route::middleware(['permission.any:master.gudang.index,master.ruangan.index,master-manajemen.master-pegawai.index,transaction.permintaan-barang.create,transaction.permintaan-barang.edit,transaction.peminjaman-barang.create,transaction.peminjaman-barang.edit,inventory.data-inventory.create,inventory.data-inventory.edit'])->group(function () {
        Route::get('api/master/gudang-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'gudangByUnit'])->name('api.master.gudang-by-unit');
        Route::get('api/master/ruangan-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'ruanganByUnit'])->name('api.master.ruangan-by-unit');
        Route::get('api/master/pegawai-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'pegawaiByUnit'])->name('api.master.pegawai-by-unit');
        Route::get('api/master/inventory-by-unit/{id_unit_kerja}', [MasterLookupController::class, 'inventoryByUnit'])->name('api.master.inventory-by-unit');
    });

    // Master Manajemen
    Route::prefix('master-manajemen')->name('master-manajemen.')->middleware(['role'])->group(function () {
        Route::resource('master-pegawai', MasterPegawaiController::class);
        Route::resource('master-jabatan', MasterJabatanController::class);
    });

    Route::prefix('master')->name('master.')->middleware(['role'])->group(function () {
        Route::resource('unit-kerja', UnitKerjaController::class);
        Route::resource('gudang', GudangController::class);
        Route::resource('ruangan', RuanganController::class);
        Route::resource('program', ProgramController::class);
        Route::resource('kegiatan', KegiatanController::class);
        Route::resource('sub-kegiatan', SubKegiatanController::class);
    });

    // Master Data
    Route::prefix('master-data')->name('master-data.')->middleware(['role'])->group(function () {
        Route::get('import-struktur-barang', [StrukturBarangImportController::class, 'index'])->name('import-struktur-barang.index');
        Route::post('import-struktur-barang', [StrukturBarangImportController::class, 'import'])->name('import-struktur-barang.import');
        Route::get('import-struktur-barang/template/download', [StrukturBarangImportController::class, 'downloadTemplate'])->name('import-struktur-barang.template');

        Route::resource('aset', AsetController::class);
        Route::resource('kode-barang', KodeBarangController::class);
        Route::resource('kategori-barang', KategoriBarangController::class);
        Route::resource('jenis-barang', JenisBarangController::class);
        Route::resource('subjenis-barang', SubjenisBarangController::class);
        Route::resource('data-barang', DataBarangController::class);
        Route::resource('satuan', SatuanController::class);
        Route::resource('sumber-anggaran', SumberAnggaranController::class);
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->middleware(['role'])->group(function () {
        Route::get('data-stock/merk-breakdown', [DataStockController::class, 'merkBreakdown'])->name('data-stock.merk-breakdown');
        Route::get('data-stock', [DataStockController::class, 'index'])->name('data-stock.index');
        Route::get('farmasi-kedaluwarsa/export', [FarmasiKedaluwarsaController::class, 'export'])->name('farmasi-kedaluwarsa.export');
        Route::get('farmasi-kedaluwarsa', [FarmasiKedaluwarsaController::class, 'index'])->name('farmasi-kedaluwarsa.index');
        Route::get('scan-qr', [InventoryItemController::class, 'scanQrPage'])->name('scan-qr');

        // Import Data Inventory
        Route::get('data-inventory/import', [InventoryDataImportController::class, 'index'])
            ->name('data-inventory.import.index');
        Route::post('data-inventory/import', [InventoryDataImportController::class, 'import'])
            ->name('data-inventory.import.import');
        Route::get('data-inventory/import/template/download', [InventoryDataImportController::class, 'downloadTemplate'])
            ->name('data-inventory.import.template');

        Route::resource('data-inventory', DataInventoryController::class);
        Route::get('inventory-item/{id}/template-qr', [InventoryItemController::class, 'templateQr'])->name('inventory-item.template-qr');
        Route::get('inventory-item/{id}/template-qr/download', [InventoryItemController::class, 'downloadTemplateQr'])->name('inventory-item.template-qr.download');
        Route::get('inventory-item/{id}/qr', [InventoryItemController::class, 'qrImage'])->name('inventory-item.qr-image');
        Route::resource('inventory-item', InventoryItemController::class);

        // Stock Adjustment
        Route::resource('stock-adjustment', StockAdjustmentController::class);
        Route::post('stock-adjustment/{id}/approve', [StockAdjustmentController::class, 'approve'])->name('stock-adjustment.approve');
        Route::post('stock-adjustment/{id}/reject', [StockAdjustmentController::class, 'reject'])->name('stock-adjustment.reject');
        Route::post('stock-adjustment/{id}/ajukan', [StockAdjustmentController::class, 'ajukan'])->name('stock-adjustment.ajukan');
    });

    // API Routes (helper AJAX — wajib permission modul terkait + scope di controller)
    Route::prefix('api')->name('api.')->group(function () {
        Route::middleware(['permission.any:master.gudang.index,master.ruangan.index,transaction.distribusi.create,transaction.distribusi.edit,transaction.draft-distribusi.create,transaction.draft-distribusi.edit'])->group(function () {
            Route::get('/gudang/{id}/ruangans', [ApiHelperController::class, 'gudangRuangans'])->name('gudang.ruangans');
        });
        Route::middleware(['permission.any:transaction.distribusi.create,transaction.distribusi.edit,transaction.distribusi.store,transaction.draft-distribusi.create,transaction.draft-distribusi.edit,transaction.compile-distribusi.index'])->group(function () {
            Route::get('/gudang/{id}/inventory', [DistribusiController::class, 'getInventoryByGudang'])->name('gudang.inventory');
            Route::get('/permintaan/{id}/detail', [DistribusiController::class, 'getPermintaanDetail'])->name('permintaan.detail');
        });
        Route::middleware(['permission.any:transaction.penerimaan-barang.create,transaction.penerimaan-barang.edit,transaction.penerimaan-barang.store'])->group(function () {
            Route::get('/distribusi/{id}/detail', [PenerimaanBarangController::class, 'getDistribusiDetail'])->name('distribusi.detail');
        });
        Route::middleware(['permission.any:inventory.data-stock.index,inventory.stock-adjustment.create,inventory.stock-adjustment.edit,inventory.data-inventory.index'])->group(function () {
            Route::get('/stock/{id}', [ApiHelperController::class, 'stockDetail'])->name('stock.detail');
        });
        Route::middleware(['permission.any:transaction.distribusi.show,transaction.distribusi.bukti-sampai,transaction.distribusi.index'])->group(function () {
            Route::get('/geocode/reverse', [ApiHelperController::class, 'reverseGeocode'])->name('geocode.reverse');
        });
    });

    // Transaction
    Route::prefix('transaction')->name('transaction.')->group(function () {
        // Permintaan Barang
        Route::resource('permintaan-barang', PermintaanBarangController::class)->middleware(['role']);
        Route::post('permintaan-barang/{id}/ajukan', [PermintaanBarangController::class, 'ajukan'])->name('permintaan-barang.ajukan')->middleware(['role']);

        // Peminjaman Barang Antar Unit / Gudang Pusat
        Route::get('peminjaman-barang', [PeminjamanBarangController::class, 'index'])->name('peminjaman-barang.index')->middleware(['role']);
        Route::get('peminjaman-barang/create', [PeminjamanBarangController::class, 'create'])->name('peminjaman-barang.create')->middleware(['role']);
        Route::post('peminjaman-barang', [PeminjamanBarangController::class, 'store'])->name('peminjaman-barang.store')->middleware(['role']);
        Route::get('peminjaman-barang/{id}', [PeminjamanBarangController::class, 'show'])->name('peminjaman-barang.show')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/verifikasi-unit-a', [PeminjamanBarangController::class, 'verifikasiUnitA'])->name('peminjaman-barang.verifikasi-unit-a')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/approve-unit-b', [PeminjamanBarangController::class, 'approveUnitB'])->name('peminjaman-barang.approve-unit-b')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/reject-unit-b', [PeminjamanBarangController::class, 'rejectUnitB'])->name('peminjaman-barang.reject-unit-b')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/approve-pengurus', [PeminjamanBarangController::class, 'approvePengurus'])->name('peminjaman-barang.approve-pengurus')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/reject-pengurus', [PeminjamanBarangController::class, 'rejectPengurus'])->name('peminjaman-barang.reject-pengurus')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/mengetahui-kasubag-tu', [PeminjamanBarangController::class, 'mengetahuiKasubagTu'])->name('peminjaman-barang.mengetahui-kasubag-tu')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/serah-terima', [PeminjamanBarangController::class, 'serahTerima'])->name('peminjaman-barang.serah-terima')->middleware(['role']);
        Route::get('pengembalian-barang', [PeminjamanBarangController::class, 'indexPengembalian'])->name('pengembalian-barang.index')->middleware(['role']);
        Route::get('peminjaman-barang/{id}/pengembalian', [PeminjamanBarangController::class, 'createPengembalian'])->name('peminjaman-barang.pengembalian.create')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/pengembalian', [PeminjamanBarangController::class, 'pengembalian'])->name('peminjaman-barang.pengembalian')->middleware(['role']);
        Route::post('peminjaman-barang/{id}/selesai', [PeminjamanBarangController::class, 'selesai'])->name('peminjaman-barang.selesai')->middleware(['role']);

        // Approval - Multi-level approval
        Route::prefix('approval')->name('approval.')->group(function () {
            Route::get('/', [ApprovalPermintaanController::class, 'index'])->name('index')->middleware(['role']);
            Route::get('/{id}', [ApprovalPermintaanController::class, 'show'])->name('show')->middleware(['role']);

            Route::post('/{id}/mengetahui', [ApprovalPermintaanController::class, 'mengetahui'])->name('mengetahui')->middleware(['role']);

            Route::post('/{id}/verifikasi', [ApprovalPermintaanController::class, 'verifikasi'])->name('verifikasi')->middleware(['role']);
            Route::post('/{id}/kembalikan', [ApprovalPermintaanController::class, 'kembalikan'])->name('kembalikan')->middleware(['role']);

            Route::post('/{id}/approve', [ApprovalPermintaanController::class, 'approve'])->name('approve')->middleware(['role']);
            Route::post('/{id}/reject', [ApprovalPermintaanController::class, 'reject'])->name('reject')->middleware(['role']);

            Route::post('/{id}/disposisi', [ApprovalPermintaanController::class, 'disposisi'])->name('disposisi')->middleware(['role']);
        });

        // Draft Distribusi
        Route::prefix('draft-distribusi')->name('draft-distribusi.')->middleware(['role'])->group(function () {
            Route::get('/', [DraftDistribusiController::class, 'index'])->name('index');
            Route::get('/create/{approvalLogId}', [DraftDistribusiController::class, 'create'])->name('create');
            Route::post('/', [DraftDistribusiController::class, 'store'])->name('store');
            Route::get('/{id}', [DraftDistribusiController::class, 'show'])->name('show');
        });

        // Compile Distribusi
        Route::prefix('compile-distribusi')->name('compile-distribusi.')->middleware(['role'])->group(function () {
            Route::get('/', [CompileDistribusiController::class, 'index'])->name('index');
            Route::get('/create/{permintaanId}', [CompileDistribusiController::class, 'create'])->name('create');
            Route::post('/', [CompileDistribusiController::class, 'store'])->name('store');
        });

        // Distribusi - single SPPB flow
        Route::get('distribusi', [DistribusiController::class, 'index'])->name('distribusi.index')->middleware(['role']);
        Route::get('distribusi/create', [DistribusiController::class, 'create'])->name('distribusi.create')->middleware(['role']);
        Route::post('distribusi', [DistribusiController::class, 'store'])->name('distribusi.store')->middleware(['role']);
        Route::get('distribusi/{id}', [DistribusiController::class, 'show'])->name('distribusi.show')->middleware(['role']);
        Route::get('distribusi/{id}/print-sbbk', [DistribusiController::class, 'printSbbk'])->name('distribusi.print-sbbk')->middleware(['role', 'feature.print-templates']);
        Route::get('distribusi/{id}/edit', [DistribusiController::class, 'edit'])->name('distribusi.edit')->middleware(['role']);
        Route::put('distribusi/{id}', [DistribusiController::class, 'update'])->name('distribusi.update')->middleware(['role']);
        Route::delete('distribusi/{id}', [DistribusiController::class, 'destroy'])->name('distribusi.destroy')->middleware(['role']);
        Route::post('distribusi/{id}/proses', [DistribusiController::class, 'proses'])->name('distribusi.proses')->middleware(['role']);
        Route::post('distribusi/{id}/kirim', [DistribusiController::class, 'kirim'])->name('distribusi.kirim')->middleware(['role']);
        Route::post('distribusi/{id}/laporkan-kedatangan', [DistribusiController::class, 'buktiSampai'])->name('distribusi.bukti-sampai')->middleware(['role']);
        Route::get('distribusi/api/gudang-tujuan/{permintaanId}', [DistribusiController::class, 'getGudangTujuanByPermintaan'])->name('distribusi.api.gudang-tujuan');

        // Penerimaan
        Route::post('penerimaan-barang/{penerimaan_barang}/verify', [PenerimaanBarangController::class, 'verify'])
            ->name('penerimaan-barang.verify')
            ->middleware(['role']);
        Route::resource('penerimaan-barang', PenerimaanBarangController::class)->except(['create', 'store'])->middleware(['role']);

        // Retur Barang
        Route::resource('retur-barang', ReturBarangController::class)->middleware(['role']);
        Route::post('retur-barang/{id}/terima', [ReturBarangController::class, 'terima'])->name('retur-barang.terima')->middleware(['role']);
        Route::post('retur-barang/{id}/tolak', [ReturBarangController::class, 'tolak'])->name('retur-barang.tolak')->middleware(['role']);
        Route::post('retur-barang/{id}/ajukan', [ReturBarangController::class, 'ajukan'])->name('retur-barang.ajukan')->middleware(['role']);
        Route::get('retur-barang/{id}/print', [ReturBarangController::class, 'printPengembalian'])->name('retur-barang.print')->middleware(['role', 'feature.print-templates']);

        // Pemakaian Barang (commented out - reserved)
        // Route::resource('pemakaian-barang', PemakaianBarangController::class)->middleware(['role']);
        // Route::post('pemakaian-barang/{id}/ajukan', [PemakaianBarangController::class, 'ajukan'])->name('pemakaian-barang.ajukan')->middleware(['role']);
        // Route::post('pemakaian-barang/{id}/approve', [PemakaianBarangController::class, 'approve'])->name('pemakaian-barang.approve')->middleware(['role']);
        // Route::post('pemakaian-barang/{id}/reject', [PemakaianBarangController::class, 'reject'])->name('pemakaian-barang.reject')->middleware(['role']);
    });

    // Asset & KIR
    Route::prefix('asset')->name('asset.')->middleware(['role'])->group(function () {
        Route::resource('register-aset', RegisterAsetController::class);
        Route::get('register-aset/unit-kerja/{unit_kerja}', [RegisterAsetController::class, 'showUnitKerja'])->name('register-aset.unit-kerja.show');

        // Kartu Inventaris Ruangan (KIR)
        Route::get('kartu-inventaris-ruangan/unit/{id_unit_kerja}/dokumen', [KartuInventarisRuanganController::class, 'dokumenUnitKerja'])->name('kartu-inventaris-ruangan.dokumen-unit');
        Route::post('kartu-inventaris-ruangan/unit/{id_unit_kerja}/dokumen/sign', [KartuInventarisRuanganController::class, 'signDokumenUnitKerja'])->name('kartu-inventaris-ruangan.dokumen-sign');
        Route::resource('kartu-inventaris-ruangan', KartuInventarisRuanganController::class);

        // Mutasi Aset
        Route::resource('mutasi-aset', MutasiAsetController::class);
    });

    // Maintenance & Pemeliharaan
    Route::prefix('maintenance')->name('maintenance.')->middleware(['role'])->group(function () {
        Route::resource('permintaan-pemeliharaan', PermintaanPemeliharaanController::class);
        Route::post('permintaan-pemeliharaan/{id}/ajukan', [PermintaanPemeliharaanController::class, 'ajukan'])->name('permintaan-pemeliharaan.ajukan');

        Route::resource('jadwal-maintenance', JadwalMaintenanceController::class);
        Route::post('jadwal-maintenance/{id}/generate-permintaan', [JadwalMaintenanceController::class, 'generatePermintaan'])
            ->name('jadwal-maintenance.generate-permintaan');

        Route::resource('kalibrasi-aset', KalibrasiAsetController::class);
        Route::resource('service-report', ServiceReportController::class);
    });

    // Planning
    Route::prefix('planning')->name('planning.')->middleware(['role'])->group(function () {
        Route::get('rekap-tahunan', [RkuController::class, 'rekapTahunan'])->name('rekap-tahunan');
        Route::resource('rku', RkuController::class);
        Route::post('rku/{rku}/submit', [RkuController::class, 'submit'])->name('rku.submit');
        Route::post('rku/{rku}/approve', [RkuController::class, 'approve'])->name('rku.approve');
        Route::post('rku/{rku}/reject', [RkuController::class, 'reject'])->name('rku.reject');
        Route::post('rku/{rku}/cancel', [RkuController::class, 'cancel'])->name('rku.cancel');
        Route::post('rku/{rku}/start-review', [RkuController::class, 'startReview'])->name('rku.startReview');
        Route::post('rku/{rku}/revise', [RkuController::class, 'revise'])->name('rku.revise');
    });

    // Procurement
    Route::prefix('procurement')->name('procurement.')->middleware(['role'])->group(function () {
        Route::get('proses-pengadaan', [ProsesPengadaanController::class, 'index'])->name('proses-pengadaan.index');
        Route::get('proses-pengadaan/{id}', [ProsesPengadaanController::class, 'show'])->name('proses-pengadaan.show');
        Route::resource('paket-pengadaan', PaketPengadaanController::class);
    });

    // Finance
    Route::prefix('finance')->name('finance.')->middleware(['role'])->group(function () {
        Route::resource('pembayaran', PembayaranController::class);
    });

    // Admin - Role & User Management
    Route::prefix('admin')->name('admin.')->middleware(['role'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::get('roles/{role}/workflow-permissions', [RoleController::class, 'editWorkflowPermissions'])->name('roles.workflow-permissions.edit');
        Route::put('roles/{role}/workflow-permissions', [RoleController::class, 'updateWorkflowPermissions'])->name('roles.workflow-permissions.update');
        Route::resource('users', UserController::class);
        Route::get('audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
        Route::get('audit-trail/{activity_log}', [AuditTrailController::class, 'show'])->name('audit-trail.show');
        Route::middleware(['feature.print-templates'])->group(function (): void {
            Route::get('print-templates/{print_template}/preview', [PrintTemplateController::class, 'preview'])->name('print-templates.preview');
            Route::get('print-templates/{print_template}/pdf', [PrintTemplateController::class, 'pdf'])->name('print-templates.pdf');
            Route::resource('print-templates', PrintTemplateController::class);
        });
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware(['role'])->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('kartu-stok/merk-breakdown', [ReportController::class, 'kartuStokMerkBreakdown'])->name('kartu-stok.merk-breakdown');
        Route::get('kartu-stok', [ReportController::class, 'kartuStok'])->name('kartu-stok');
        Route::get('kartu-stok/export', [ReportController::class, 'exportKartuStok'])->name('kartu-stok.export');
        Route::get('stock-gudang', [ReportController::class, 'stockGudang'])->name('stock-gudang');
        Route::get('stock-gudang/export', [ReportController::class, 'exportStockGudang'])->name('stock-gudang.export');
        Route::get('transaksi-summary', [ReportController::class, 'transaksiSummary'])->name('transaksi-summary');
        Route::get('transaksi-summary/export', [ReportController::class, 'exportTransaksiSummary'])->name('transaksi-summary.export');
        Route::get('aset-summary', [ReportController::class, 'asetSummary'])->name('aset-summary');
        Route::get('aset-summary/export', [ReportController::class, 'exportAsetSummary'])->name('aset-summary.export');
        Route::get('maintenance-summary', [ReportController::class, 'maintenanceSummary'])->name('maintenance-summary');
        Route::get('maintenance-summary/export', [ReportController::class, 'exportMaintenanceSummary'])->name('maintenance-summary.export');
    });
});
