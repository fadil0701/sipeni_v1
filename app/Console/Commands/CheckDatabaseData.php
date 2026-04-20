<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckDatabaseData extends Command
{
    protected $signature = 'db:check-data {--table= : Check specific table only} {--connection= : Database connection (mysql, sqlite)}';
    
    protected $description = 'Check all data in database - show table names and row counts';

    public function handle()
    {
        $connection = $this->option('connection') ?: config('database.default');
        
        // Fix SQLite path if needed
        if ($connection === 'sqlite') {
            $sqlitePath = database_path('database.sqlite');
            if (file_exists($sqlitePath)) {
                config(["database.connections.sqlite.database" => $sqlitePath]);
            }
        }
        
        $databaseName = $this->getDatabaseName($connection);
        $this->info("Connection: {$connection}");
        $this->info("Database: {$databaseName}");
        $this->newLine();
        
        // Get all tables
        $tables = $this->getTables($connection);
        
        $tableData = [];
        $totalRows = 0;
        
        foreach ($tables as $tableName) {
            // Skip migrations table
            if ($tableName === 'migrations') {
                continue;
            }
            
            // If specific table requested, skip others
            if ($this->option('table') && $tableName !== $this->option('table')) {
                continue;
            }
            
            try {
                $rowCount = DB::connection($connection)->table($tableName)->count();
                $tableData[] = [
                    'table' => $tableName,
                    'rows' => $rowCount,
                ];
                $totalRows += $rowCount;
            } catch (\Exception $e) {
                $tableData[] = [
                    'table' => $tableName,
                    'rows' => 'ERROR: ' . $e->getMessage(),
                ];
            }
        }
        
        // Sort by table name
        usort($tableData, function($a, $b) {
            return strcmp($a['table'], $b['table']);
        });
        
        // Display results
        $this->table(['Table Name', 'Row Count'], $tableData);
        $this->newLine();
        $this->info("Total Tables: " . count($tableData));
        $this->info("Total Rows: " . number_format($totalRows));
        
        return Command::SUCCESS;
    }
    
    private function getDatabaseName($connection)
    {
        $config = config("database.connections.{$connection}");
        
        if ($connection === 'sqlite') {
            $path = $config['database'] ?? database_path('database.sqlite');
            // If path is relative, make it absolute
            if (!file_exists($path) && !str_starts_with($path, '/') && !preg_match('/^[A-Z]:/', $path)) {
                $path = database_path('database.sqlite');
            }
            return file_exists($path) ? basename($path) : 'not found';
        }
        
        return $config['database'] ?? 'unknown';
    }
    
    private function getTables($connection)
    {
        if ($connection === 'sqlite') {
            $tables = DB::connection($connection)->select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            return array_map(function($table) {
                return $table->name;
            }, $tables);
        }
        
        // MySQL
        $databaseName = config("database.connections.{$connection}.database");
        $tables = DB::connection($connection)->select("SHOW TABLES");
        $tableKey = "Tables_in_{$databaseName}";
        
        return array_map(function($table) use ($tableKey) {
            return $table->$tableKey;
        }, $tables);
    }
}
