<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ClearSqliteData extends Command
{
    protected $signature = 'db:clear-sqlite {--delete-file : Delete SQLite file completely}';
    
    protected $description = 'Clear all data from SQLite database or delete SQLite file';

    public function handle()
    {
        $sqlitePath = database_path('database.sqlite');
        
        if (!file_exists($sqlitePath)) {
            $this->info('SQLite database file does not exist.');
            return Command::SUCCESS;
        }
        
        $this->info('SQLite Database: ' . basename($sqlitePath));
        $this->info('File Size: ' . number_format(filesize($sqlitePath)) . ' bytes');
        $this->newLine();
        
        if ($this->option('delete-file')) {
            // Delete file completely
            if (!$this->confirm('Apakah Anda yakin ingin MENGHAPUS file SQLite? Tindakan ini tidak dapat dibatalkan!', false)) {
                $this->info('Operasi dibatalkan.');
                return Command::SUCCESS;
            }
            
            try {
                // Set connection to sqlite temporarily
                config(["database.connections.sqlite.database" => $sqlitePath]);
                
                // Get all tables
                $tables = DB::connection('sqlite')->select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
                
                $this->info('Menghapus file SQLite...');
                
                // Close any open connections
                DB::purge('sqlite');
                
                // Delete file
                if (File::delete($sqlitePath)) {
                    $this->info('✓ File SQLite berhasil dihapus: ' . basename($sqlitePath));
                    $this->info('Total tables yang akan terhapus: ' . count($tables));
                    return Command::SUCCESS;
                } else {
                    $this->error('Gagal menghapus file SQLite.');
                    return Command::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error('Terjadi kesalahan: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            // Clear all data but keep file
            if (!$this->confirm('Apakah Anda yakin ingin MENGHAPUS SEMUA DATA dari SQLite? Tindakan ini tidak dapat dibatalkan!', false)) {
                $this->info('Operasi dibatalkan.');
                return Command::SUCCESS;
            }
            
            try {
                // Set connection to sqlite
                config(["database.connections.sqlite.database" => $sqlitePath]);
                
                // Get all tables
                $tables = DB::connection('sqlite')->select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
                
                $this->info('Menghapus semua data dari SQLite...');
                $this->newLine();
                
                DB::connection('sqlite')->beginTransaction();
                
                $totalDeleted = 0;
                foreach ($tables as $table) {
                    $tableName = $table->name;
                    
                    // Skip migrations table
                    if ($tableName === 'migrations') {
                        continue;
                    }
                    
                    try {
                        $count = DB::connection('sqlite')->table($tableName)->count();
                        if ($count > 0) {
                            DB::connection('sqlite')->table($tableName)->delete();
                            $this->line("  ✓ {$tableName}: {$count} record(s) dihapus");
                            $totalDeleted += $count;
                        }
                    } catch (\Exception $e) {
                        $this->line("  ⊘ {$tableName}: Error - " . $e->getMessage());
                    }
                }
                
                // Reset auto-increment sequences
                DB::connection('sqlite')->statement("DELETE FROM sqlite_sequence");
                
                DB::connection('sqlite')->commit();
                
                $this->newLine();
                $this->info("✓ Semua data berhasil dihapus!");
                $this->info("Total records dihapus: " . number_format($totalDeleted));
                $this->info("File SQLite tetap ada tetapi kosong.");
                
                return Command::SUCCESS;
                
            } catch (\Exception $e) {
                DB::connection('sqlite')->rollBack();
                $this->error('Terjadi kesalahan: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }
    }
}
