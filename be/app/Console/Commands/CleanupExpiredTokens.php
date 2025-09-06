<?php

namespace App\Console\Commands;

use App\Models\RefreshToken;
use Illuminate\Console\Command;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup {--days=30 : Delete tokens older than X days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired refresh tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("Cleaning up expired refresh tokens...");

        // Delete expired tokens
        $expiredCount = RefreshToken::expired()->count();
        RefreshToken::expired()->delete();

        // Delete tokens older than specified days (even if not expired)
        $oldTokensCount = RefreshToken::where('created_at', '<', now()->subDays($days))->count();
        RefreshToken::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$expiredCount} expired tokens");
        $this->info("Deleted {$oldTokensCount} tokens older than {$days} days");
        $this->info("Token cleanup completed successfully!");

        return 0;
    }
}
