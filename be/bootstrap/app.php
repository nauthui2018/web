<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckTestOwnership;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->namespace('App\Http\Controllers\Api\v1')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
        commands: __DIR__.'/../routes/console.php',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Only schedule if cleanup is enabled
        if (!config('token_cleanup.enabled', true)) {
            return;
        }

        $frequency = config('token_cleanup.frequency', 'daily');
        $time = config('token_cleanup.time', '02:00');
        $retentionDays = config('token_cleanup.retention_days', 30);
        $logEnabled = config('token_cleanup.log_enabled', true);
        $logFile = config('token_cleanup.log_file') ?: storage_path('logs/token-cleanup.log');

        // Build the command with retention days
        $command = "tokens:cleanup --days={$retentionDays}";

        // Create the scheduled command based on frequency
        $scheduledCommand = match($frequency) {
            'hourly' => $schedule->command($command)->hourly(),
            'daily' => $schedule->command($command)->dailyAt($time),
            'weekly' => $schedule->command($command)->weeklyOn(0, $time),
            'monthly' => $schedule->command($command)->monthlyOn(1, $time),
            'twiceDaily' => $schedule->command($command)->twiceDaily(2, 14),
            'everyMinute' => $schedule->command($command)->everyMinute(),
            default => $schedule->command($command)->dailyAt($time),
        };

        // Apply additional options
        if (config('token_cleanup.options.without_overlapping', true)) {
            $scheduledCommand->withoutOverlapping();
        }

        if (config('token_cleanup.options.run_in_background', true)) {
            $scheduledCommand->runInBackground();
        }

        if ($logEnabled) {
            $scheduledCommand->appendOutputTo($logFile);
        }
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('api', ForceJsonResponse::class);

        // Register middleware aliases
        $middleware->alias([
            'role' => CheckRole::class,
            'check.user.status' => CheckUserStatus::class,
            'check.test.ownership' => CheckTestOwnership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
