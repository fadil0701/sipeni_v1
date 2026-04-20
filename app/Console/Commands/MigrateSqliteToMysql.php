<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;
use App\Enums\PermintaanBarangStatus;

class MigrateSqliteToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:sqlite-to-mysql 
                            {--force : Force migration without confirmation}
                            {--skip-data : Skip data migration, only migrate schema}
                            {--skip-migrations : Skip running migrations (assume tables already exist)}
                            {--truncate : Truncate tables before migrating (WARNING: This will delete existing data)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from SQLite to MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if MySQL connection is configured
        if (!config('database.connections.mysql.database')) {
            $this->error('MySQL database not configured. Please check your .env file.');
            $this->info('Required MySQL config in .env:');
            $this->info('DB_CONNECTION=mysql');
            $this->info('DB_HOST=127.0.0.1');
            $this->info('DB_PORT=3306');
            $this->info('DB_DATABASE=your_database_name');
            $this->info('DB_USERNAME=your_username');
            $this->info('DB_PASSWORD=your_password');
            return 1;
        }

        // Check if SQLite database exists
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error('SQLite database not found at: ' . $sqlitePath);
            return 1;
        }

        // Check if SQLite database exists
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error('SQLite database not found at: ' . $sqlitePath);
            return 1;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('This will migrate all data from SQLite to MySQL. Continue?')) {
                $this->info('Migration cancelled.');
                return 0;
            }
        }

        $this->info('Starting migration from SQLite to MySQL...');
        $this->newLine();

        try {
            // Step 1: Run migrations on MySQL (create tables)
            if (!$this->option('skip-migrations')) {
                $this->info('Step 1: Running migrations on MySQL...');
                $this->info('  Note: This will create/update tables in MySQL database.');
                
                // Temporarily switch to MySQL for migrations
                $originalConnection = config('database.default');
                config(['database.default' => 'mysql']);
                
                try {
                    // Check if migrations table exists, if not, install it
                    if (!Schema::connection('mysql')->hasTable('migrations')) {
                        $this->call('migrate:install', ['--force' => true]);
                    }
                    
                    // Run migrations (will skip if already migrated)
                    $this->call('migrate', [
                        '--force' => true,
                    ]);
                    $this->info('✓ Migrations completed');
                } catch (\Exception $e) {
                    // If migration fails because table exists, continue anyway
                    if (str_contains($e->getMessage(), 'already exists')) {
                        $this->warn('  Some tables already exist, continuing with data migration...');
                    } else {
                        $this->error('  Migration error: ' . $e->getMessage());
                        $this->warn('  You can use --skip-migrations flag to skip this step if tables already exist.');
                        throw $e;
                    }
                } finally {
                    // Restore original connection
                    config(['database.default' => $originalConnection]);
                }
                $this->newLine();
            } else {
                $this->info('Step 1: Skipping migrations (--skip-migrations flag set)');
                $this->newLine();
            }

            if ($this->option('skip-data')) {
                $this->info('Skipping data migration (--skip-data flag set).');
                return 0;
            }

            // Step 2: Get all tables from SQLite
            $this->info('Step 2: Reading tables from SQLite...');
            $tables = $this->getSqliteTables();
            $this->info('✓ Found ' . count($tables) . ' tables');
            $this->newLine();

            // Step 3: Define table order (to avoid foreign key constraints)
            $tableOrder = $this->getTableOrder($tables);
            
            // Step 4: Migrate data table by table
            $this->info('Step 3: Migrating data...');
            $totalRecords = 0;
            
            foreach ($tableOrder as $table) {
                if (!in_array($table, $tables)) {
                    continue;
                }

                $this->info("  Migrating table: {$table}...");
                
                try {
                    $count = $this->migrateTable($table);
                    $totalRecords += $count;
                    $this->info("  ✓ Migrated {$count} records from {$table}");
                } catch (Exception $e) {
                    $this->error("  ✗ Error migrating {$table}: " . $e->getMessage());
                    // Continue with next table
                }
            }

            $this->newLine();
            $this->info("✓ Migration completed! Total records migrated: {$totalRecords}");
            $this->newLine();
            $this->info('Next steps:');
            $this->info('1. Update .env file: DB_CONNECTION=mysql');
            $this->info('2. Test your application');
            $this->info('3. Backup SQLite database before removing it');

            return 0;
        } catch (Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Get all tables from SQLite database
     */
    private function getSqliteTables(): array
    {
        // Connect directly to SQLite file
        $sqlitePath = database_path('database.sqlite');
        $pdo = new \PDO("sqlite:{$sqlitePath}");
        
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        return $tables;
    }

    /**
     * Get table order to avoid foreign key constraint errors
     */
    private function getTableOrder(array $tables): array
    {
        // Define order based on dependencies
        // Tables without foreign keys first, then tables with foreign keys
        $ordered = [
            // Skip migrations table - it will be handled separately
            // 'migrations', // Skip - will be handled by Laravel migrations
            
            // Master tables first (no dependencies)
            'roles',
            'permissions',
            'modules',
            'master_aset',
            'master_kode_barang',
            'master_kategori_barang',
            'master_jenis_barang',
            'master_subjenis_barang',
            'master_satuan',
            'master_sumber_anggaran',
            'master_unit_kerja',
            'master_jabatan',
            'master_lokasi',
            'master_gudang',
            'master_ruangan',
            'master_program',
            'master_kegiatan',
            'master_sub_kegiatan',
            'users',
            'master_pegawai',
            'user_modules',
            'permission_role',
            'role_user',
            
            // Then tables with dependencies
            'master_data_barang',
            'data_inventory',
            'inventory_item',
            'data_stock',
            'register_aset',
            'kartu_inventaris_ruangan',
            'mutasi_aset',
            'permintaan_barang',
            'approval_log',
            'approval_flow_definition',
            'draft_distribusi',
            'detail_draft_distribusi',
            'transaksi_distribusi',
            'detail_distribusi',
            'penerimaan_barang',
            'detail_penerimaan_barang',
            'retur_barang',
            'detail_retur_barang',
            'permintaan_pemeliharaan',
            'jadwal_maintenance',
            'kalibrasi_aset',
            'service_report',
            'riwayat_pemeliharaan',
        ];

        // Add any tables not in the ordered list
        foreach ($tables as $table) {
            if (!in_array($table, $ordered)) {
                $ordered[] = $table;
            }
        }

        return $ordered;
    }

    /**
     * Migrate data from one table
     */
    private function migrateTable(string $table): int
    {
        // Skip migrations table - it's handled by Laravel
        if ($table === 'migrations') {
            return 0;
        }

        // Check if table exists in MySQL
        if (!Schema::connection('mysql')->hasTable($table)) {
            $this->warn("  Table {$table} does not exist in MySQL, skipping...");
            return 0;
        }

        // Get column names from MySQL to ensure compatibility
        $mysqlColumns = Schema::connection('mysql')->getColumnListing($table);
        
        // Get all data from SQLite file directly
        $sqlitePath = database_path('database.sqlite');
        $pdo = new \PDO("sqlite:{$sqlitePath}");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Get column info
        $stmt = $pdo->query("PRAGMA table_info({$table})");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'name');
        
        // Get all data
        $stmt = $pdo->query("SELECT * FROM {$table}");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Convert to collection-like structure
        $data = collect($rows);
        
        if ($data->isEmpty()) {
            return 0;
        }

        // Disable foreign key checks temporarily
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Clear existing data if --truncate flag is set
            if ($this->option('truncate')) {
                DB::connection('mysql')->table($table)->truncate();
            }

            // Insert data in chunks
            $chunks = $data->chunk(100);
            $count = 0;

            foreach ($chunks as $chunk) {
                $records = [];
                
                foreach ($chunk as $record) {
                    $recordArray = (array) $record;
                    
                    // Filter to only include columns that exist in MySQL
                    $filteredRecord = [];
                    foreach ($mysqlColumns as $column) {
                        if (array_key_exists($column, $recordArray)) {
                            $filteredRecord[$column] = $recordArray[$column];
                        }
                    }
                    
                    if (!empty($filteredRecord)) {
                        $records[] = $this->prepareRecord($filteredRecord, $table);
                    }
                }

                if (!empty($records)) {
                    try {
                        // Special handling for approval_flow_definition to avoid duplicates
                        if ($table === 'approval_flow_definition') {
                            foreach ($records as $record) {
                                try {
                                    DB::connection('mysql')->table($table)->insert($record);
                                    $count++;
                                } catch (\Exception $e) {
                                    if (str_contains($e->getMessage(), 'Duplicate entry')) {
                                        // Skip duplicate entries
                                        $this->warn("    Skipping duplicate entry for approval_flow_definition");
                                    } else {
                                        throw $e;
                                    }
                                }
                            }
                        } else {
                            DB::connection('mysql')->table($table)->insert($records);
                            $count += count($records);
                        }
                    } catch (\Exception $e) {
                        // If bulk insert fails, try one by one
                        $errorMsg = $e->getMessage();
                        if (str_contains($errorMsg, 'Data truncated') || 
                            str_contains($errorMsg, 'Duplicate entry') ||
                            str_contains($errorMsg, 'Integrity constraint')) {
                            $this->warn("    Bulk insert failed: " . substr($errorMsg, 0, 100) . "... trying individual inserts...");
                            foreach ($records as $record) {
                                try {
                                    DB::connection('mysql')->table($table)->insert($record);
                                    $count++;
                                } catch (\Exception $e2) {
                                    $errorMsg2 = $e2->getMessage();
                                    if (str_contains($errorMsg2, 'Duplicate entry')) {
                                        // Skip duplicates silently (data already exists)
                                        continue;
                                    } elseif (str_contains($errorMsg2, 'Data truncated')) {
                                        // Try to fix the record and retry
                                        $this->warn("    Data truncated for record, skipping...");
                                        // Skip this record for now
                                        continue;
                                    } else {
                                        // Only show error if it's not a duplicate
                                        if (!str_contains($errorMsg2, 'Duplicate')) {
                                            $this->warn("    Skipping record due to error: " . substr($errorMsg2, 0, 100));
                                        }
                                        // Continue with next record
                                        continue;
                                    }
                                }
                            }
                        } else {
                            // Unknown error, show it
                            $this->error("    Unexpected error: " . substr($errorMsg, 0, 200));
                            // Try individual inserts as fallback
                            foreach ($records as $record) {
                                try {
                                    DB::connection('mysql')->table($table)->insert($record);
                                    $count++;
                                } catch (\Exception $e2) {
                                    if (!str_contains($e2->getMessage(), 'Duplicate')) {
                                        continue; // Skip this record
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $count;
        } finally {
            // Re-enable foreign key checks
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Prepare a single record for MySQL insertion
     */
    private function prepareRecord(array $record, string $table): array
    {
        foreach ($record as $key => &$value) {
            // Convert boolean to integer
            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }
            
            // Handle empty strings for nullable fields
            if ($value === '') {
                $value = null;
            }

            if ($table === 'permintaan_barang') {
                if ($key === 'jenis_permintaan') {
                    // jenis_permintaan is now JSON, handle it properly
                    if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                        // Already JSON string, keep as is
                    } elseif (is_string($value)) {
                        // Convert single value to JSON array
                        $value = json_encode([$value]);
                    } elseif (is_array($value)) {
                        // Convert array to JSON string
                        $value = json_encode($value);
                    }
                }
            }

            // Handle approval_flow_definition duplicate entries
            if ($table === 'approval_flow_definition') {
                // Skip if this would cause duplicate - we'll handle it separately
            }

            // SQLite stores booleans as 0/1, which is fine for MySQL
            // SQLite datetime format is compatible with MySQL
        }

        if ($table === 'permintaan_barang') {
            if (array_key_exists('status_permintaan', $record)) {
                $record['status'] = PermintaanBarangStatus::normalizeStored(
                    $record['status_permintaan'] !== null ? (string) $record['status_permintaan'] : null
                )->value;
                unset($record['status_permintaan']);
            } elseif (array_key_exists('status', $record)) {
                $record['status'] = PermintaanBarangStatus::normalizeStored(
                    $record['status'] !== null ? (string) $record['status'] : null
                )->value;
            }
        }

        return $record;
    }
}
