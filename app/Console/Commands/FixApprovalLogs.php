<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixApprovalLogs extends Command
{
    protected $signature = 'approval:fix-logs';
    protected $description = 'Deprecated: approval logs are now auto-synced at application level';

    public function handle()
    {
        $this->warn('Command deprecated.');
        $this->line('Approval log integrity is now enforced automatically in service/observer layer.');
        $this->line('Use `php artisan approval:check` only for diagnostics.');
        return 0;
    }
}
