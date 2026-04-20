<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PenerimaanBarang;
use App\Models\DetailPenerimaanBarang;
use App\Models\TransaksiDistribusi;
use App\Models\DetailDistribusi;
use App\Models\DraftDetailDistribusi;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\DetailPermintaanBarang;

class CleanupTransactionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:cleanup {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus semua data transaksi dari permintaan sampai penerimaan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin menghapus semua data transaksi (Permintaan, Approval, Distribusi, Draft Distribusi, Penerimaan, Retur, History Lokasi, dll)?')) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $this->info('Memulai penghapusan data transaksi...');
        $this->newLine();

        // Nonaktifkan foreign key check untuk MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            $results = [];

            // Urutan penghapusan berdasarkan foreign key dependencies
            // Mulai dari tabel detail/child terlebih dahulu

            // 1. Hapus Detail Retur Barang
            $this->info('Menghapus Detail Retur Barang...');
            $count = DB::table('detail_retur_barang')->count();
            DB::table('detail_retur_barang')->truncate();
            $results[] = ['Detail Retur Barang', $count];
            $this->info("✓ {$count} detail retur barang dihapus.");

            // 2. Hapus Retur Barang
            $this->info('Menghapus Retur Barang...');
            $count = DB::table('retur_barang')->count();
            DB::table('retur_barang')->truncate();
            $results[] = ['Retur Barang', $count];
            $this->info("✓ {$count} retur barang dihapus.");

            // 3. Hapus Detail Penerimaan Barang
            $this->info('Menghapus Detail Penerimaan Barang...');
            $count = DetailPenerimaanBarang::count();
            DetailPenerimaanBarang::truncate();
            $results[] = ['Detail Penerimaan Barang', $count];
            $this->info("✓ {$count} detail penerimaan barang dihapus.");

            // 4. Hapus Penerimaan Barang
            $this->info('Menghapus Penerimaan Barang...');
            $count = PenerimaanBarang::count();
            PenerimaanBarang::truncate();
            $results[] = ['Penerimaan Barang', $count];
            $this->info("✓ {$count} penerimaan barang dihapus.");

            // 5. Hapus History Lokasi
            $this->info('Menghapus History Lokasi...');
            $count = DB::table('history_lokasi')->count();
            DB::table('history_lokasi')->truncate();
            $results[] = ['History Lokasi', $count];
            $this->info("✓ {$count} history lokasi dihapus.");

            // 6. Hapus Detail Distribusi
            $this->info('Menghapus Detail Distribusi...');
            $count = DetailDistribusi::count();
            DetailDistribusi::truncate();
            $results[] = ['Detail Distribusi', $count];
            $this->info("✓ {$count} detail distribusi dihapus.");

            // 7. Hapus Draft Detail Distribusi
            $this->info('Menghapus Draft Detail Distribusi...');
            $count = DraftDetailDistribusi::count();
            DraftDetailDistribusi::truncate();
            $results[] = ['Draft Detail Distribusi', $count];
            $this->info("✓ {$count} draft detail distribusi dihapus.");

            // 8. Hapus Transaksi Distribusi
            $this->info('Menghapus Transaksi Distribusi...');
            $count = TransaksiDistribusi::count();
            TransaksiDistribusi::truncate();
            $results[] = ['Transaksi Distribusi', $count];
            $this->info("✓ {$count} transaksi distribusi dihapus.");

            // 9. Hapus Approval Log (harus sebelum approval_permintaan jika ada)
            $this->info('Menghapus Approval Log...');
            $count = ApprovalLog::count();
            ApprovalLog::truncate();
            $results[] = ['Approval Log', $count];
            $this->info("✓ {$count} approval log dihapus.");

            // 10. Hapus Approval Permintaan (tabel lama jika masih ada)
            if (DB::getSchemaBuilder()->hasTable('approval_permintaan')) {
                $this->info('Menghapus Approval Permintaan...');
                $count = DB::table('approval_permintaan')->count();
                DB::table('approval_permintaan')->truncate();
                $results[] = ['Approval Permintaan', $count];
                $this->info("✓ {$count} approval permintaan dihapus.");
            }

            // 11. Hapus Detail Permintaan Barang
            $this->info('Menghapus Detail Permintaan Barang...');
            $count = DetailPermintaanBarang::count();
            DetailPermintaanBarang::truncate();
            $results[] = ['Detail Permintaan Barang', $count];
            $this->info("✓ {$count} detail permintaan barang dihapus.");

            // 12. Hapus Permintaan Barang
            $this->info('Menghapus Permintaan Barang...');
            $count = PermintaanBarang::count();
            PermintaanBarang::truncate();
            $results[] = ['Permintaan Barang', $count];
            $this->info("✓ {$count} permintaan barang dihapus.");

            // 13. Hapus Permintaan Pemeliharaan (jika ada)
            if (DB::getSchemaBuilder()->hasTable('permintaan_pemeliharaan')) {
                $this->info('Menghapus Permintaan Pemeliharaan...');
                $count = DB::table('permintaan_pemeliharaan')->count();
                DB::table('permintaan_pemeliharaan')->truncate();
                $results[] = ['Permintaan Pemeliharaan', $count];
                $this->info("✓ {$count} permintaan pemeliharaan dihapus.");
            }

            // 14. Hapus Riwayat Pemeliharaan (jika ada)
            if (DB::getSchemaBuilder()->hasTable('riwayat_pemeliharaan')) {
                $this->info('Menghapus Riwayat Pemeliharaan...');
                $count = DB::table('riwayat_pemeliharaan')->count();
                DB::table('riwayat_pemeliharaan')->truncate();
                $results[] = ['Riwayat Pemeliharaan', $count];
                $this->info("✓ {$count} riwayat pemeliharaan dihapus.");
            }

            // 15. Hapus Service Report (jika ada)
            if (DB::getSchemaBuilder()->hasTable('service_report')) {
                $this->info('Menghapus Service Report...');
                $count = DB::table('service_report')->count();
                DB::table('service_report')->truncate();
                $results[] = ['Service Report', $count];
                $this->info("✓ {$count} service report dihapus.");
            }

            // 16. Hapus Jadwal Maintenance (jika ada)
            if (DB::getSchemaBuilder()->hasTable('jadwal_maintenance')) {
                $this->info('Menghapus Jadwal Maintenance...');
                $count = DB::table('jadwal_maintenance')->count();
                DB::table('jadwal_maintenance')->truncate();
                $results[] = ['Jadwal Maintenance', $count];
                $this->info("✓ {$count} jadwal maintenance dihapus.");
            }

            // 17. Hapus Kalibrasi Aset (jika ada)
            if (DB::getSchemaBuilder()->hasTable('kalibrasi_aset')) {
                $this->info('Menghapus Kalibrasi Aset...');
                $count = DB::table('kalibrasi_aset')->count();
                DB::table('kalibrasi_aset')->truncate();
                $results[] = ['Kalibrasi Aset', $count];
                $this->info("✓ {$count} kalibrasi aset dihapus.");
            }

            // 18. Hapus Mutasi Aset
            if (DB::getSchemaBuilder()->hasTable('mutasi_aset')) {
                $this->info('Menghapus Mutasi Aset...');
                $count = DB::table('mutasi_aset')->count();
                DB::table('mutasi_aset')->truncate();
                $results[] = ['Mutasi Aset', $count];
                $this->info("✓ {$count} mutasi aset dihapus.");
            }

            // 19. Hapus Kartu Inventaris Ruangan
            if (DB::getSchemaBuilder()->hasTable('kartu_inventaris_ruangan')) {
                $this->info('Menghapus Kartu Inventaris Ruangan...');
                $count = DB::table('kartu_inventaris_ruangan')->count();
                DB::table('kartu_inventaris_ruangan')->truncate();
                $results[] = ['Kartu Inventaris Ruangan', $count];
                $this->info("✓ {$count} kartu inventaris ruangan dihapus.");
            }

            // 20. Hapus Register Aset
            if (DB::getSchemaBuilder()->hasTable('register_aset')) {
                $this->info('Menghapus Register Aset...');
                $count = DB::table('register_aset')->count();
                DB::table('register_aset')->truncate();
                $results[] = ['Register Aset', $count];
                $this->info("✓ {$count} register aset dihapus.");
            }

            // 21. Hapus Inventory Item
            if (DB::getSchemaBuilder()->hasTable('inventory_item')) {
                $this->info('Menghapus Inventory Item...');
                $count = DB::table('inventory_item')->count();
                DB::table('inventory_item')->truncate();
                $results[] = ['Inventory Item', $count];
                $this->info("✓ {$count} inventory item dihapus.");
            }

            // 22. Hapus Data Stock Opname
            if (DB::getSchemaBuilder()->hasTable('data_stock_opname')) {
                $this->info('Menghapus Data Stock Opname...');
                $count = DB::table('data_stock_opname')->count();
                DB::table('data_stock_opname')->truncate();
                $results[] = ['Data Stock Opname', $count];
                $this->info("✓ {$count} data stock opname dihapus.");
            }

            // 23. Reset Data Stock (set qty ke 0)
            if (DB::getSchemaBuilder()->hasTable('data_stock')) {
                $this->info('Reset Data Stock...');
                $count = DB::table('data_stock')->count();
                DB::table('data_stock')->update([
                    'qty_awal' => 0,
                    'qty_masuk' => 0,
                    'qty_keluar' => 0,
                    'qty_akhir' => 0,
                    'last_updated' => now()
                ]);
                $results[] = ['Data Stock (Reset)', $count];
                $this->info("✓ {$count} data stock di-reset.");
            }

            // 24. Reset Data Inventory (opsional - hanya jika user mau)
            // Uncomment jika ingin menghapus data inventory juga
            // if (DB::getSchemaBuilder()->hasTable('data_inventory')) {
            //     $this->info('Menghapus Data Inventory...');
            //     $count = DB::table('data_inventory')->count();
            //     DB::table('data_inventory')->truncate();
            //     $results[] = ['Data Inventory', $count];
            //     $this->info("✓ {$count} data inventory dihapus.");
            // }

            // Aktifkan kembali foreign key check
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->newLine();
            $this->info('✓ Semua data transaksi berhasil dihapus!');
            $this->newLine();
            $this->table(
                ['Tabel', 'Jumlah Data Dihapus'],
                $results
            );

            $totalDeleted = array_sum(array_column($results, 1));
            $this->newLine();
            $this->info("Total data yang dihapus: {$totalDeleted} records");

            return 0;
        } catch (\Exception $e) {
            // Aktifkan kembali foreign key check jika terjadi error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->error('Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
