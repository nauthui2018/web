<?php

namespace App\Jobs;

use App\Models\RefreshToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $days;

    /**
     * Create a new job instance.
     */
    public function __construct(int $days = 30)
    {
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting token cleanup job...");
        
        // Delete expired tokens
        $expiredCount = RefreshToken::expired()->count();
        RefreshToken::expired()->delete();
        
        // Delete tokens older than specified days
        $oldTokensCount = RefreshToken::where('created_at', '<', now()->subDays($this->days))->count();
        RefreshToken::where('created_at', '<', now()->subDays($this->days))->delete();
        
        Log::info("Token cleanup completed", [
            'expired_tokens_deleted' => $expiredCount,
            'old_tokens_deleted' => $oldTokensCount,
            'days_threshold' => $this->days
        ]);
    }
}
