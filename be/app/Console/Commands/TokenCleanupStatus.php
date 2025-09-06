<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TokenCleanupStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current token cleanup scheduler configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Token Cleanup Scheduler Configuration');
        $this->info('=====================================');

        $enabled = config('token_cleanup.enabled') ? 'Enabled' : 'Disabled';
        $frequency = config('token_cleanup.frequency');
        $time = config('token_cleanup.time');
        $retentionDays = config('token_cleanup.retention_days');
        $logEnabled = config('token_cleanup.log_enabled') ? 'Enabled' : 'Disabled';
        $logFile = config('token_cleanup.log_file') ?: storage_path('logs/token-cleanup.log');

        $this->table(['Setting', 'Value'], [
            ['Status', $enabled],
            ['Frequency', $frequency],
            ['Time', $time],
            ['Retention Days', $retentionDays],
            ['Logging', $logEnabled],
            ['Log File', $logFile],
        ]);

        if (config('token_cleanup.enabled')) {
            $this->info('');
            $this->info('Current Schedule:');
            $this->call('schedule:list');
        } else {
            $this->warn('');
            $this->warn('⚠️  Token cleanup is currently DISABLED');
            $this->warn('   Set TOKEN_CLEANUP_ENABLED=true in .env to enable');
        }

        return 0;
    }
}
