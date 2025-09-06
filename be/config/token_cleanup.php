<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Token Cleanup Scheduler Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the automated token cleanup
    | scheduler. You can control when and how the cleanup runs via environment
    | variables.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cleanup Enabled
    |--------------------------------------------------------------------------
    |
    | Whether the token cleanup scheduler is enabled. Set to false to disable
    | automatic cleanup entirely.
    |
    */
    'enabled' => env('TOKEN_CLEANUP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cleanup Frequency
    |--------------------------------------------------------------------------
    |
    | How often the cleanup should run. Supported values:
    | - 'hourly' : Every hour
    | - 'daily'  : Once per day (default)
    | - 'weekly' : Once per week (Sunday)
    | - 'monthly': Once per month (1st day)
    | - 'twiceDaily': Twice per day (2 AM and 2 PM)
    | - 'everyMinute': Every minute (for testing only)
    |
    */
    'frequency' => env('TOKEN_CLEANUP_FREQUENCY', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Cleanup Time
    |--------------------------------------------------------------------------
    |
    | What time the cleanup should run (for daily frequency).
    | Format: HH:MM (24-hour format)
    | Example: '02:00' for 2:00 AM
    |
    */
    'time' => env('TOKEN_CLEANUP_TIME', '02:00'),

    /*
    |--------------------------------------------------------------------------
    | Token Retention Days
    |--------------------------------------------------------------------------
    |
    | How many days to keep tokens before deleting them (even if not expired).
    | This helps prevent the database from growing too large.
    |
    */
    'retention_days' => (int) env('TOKEN_CLEANUP_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Whether to log cleanup operations to a separate log file.
    | Log file will be stored at: storage/logs/token-cleanup.log
    |
    */
    'log_enabled' => env('TOKEN_CLEANUP_LOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log File Path
    |--------------------------------------------------------------------------
    |
    | Custom log file path for cleanup operations.
    | Leave null to use default: storage/logs/token-cleanup.log
    |
    */
    'log_file' => env('TOKEN_CLEANUP_LOG_FILE', null),

    /*
    |--------------------------------------------------------------------------
    | Scheduler Options
    |--------------------------------------------------------------------------
    |
    | Additional scheduler configuration options.
    |
    */
    'options' => [
        // Prevent overlapping executions
        'without_overlapping' => env('TOKEN_CLEANUP_WITHOUT_OVERLAPPING', true),
        
        // Run in background
        'run_in_background' => env('TOKEN_CLEANUP_RUN_IN_BACKGROUND', true),
        
        // Maximum execution time in seconds (for testing)
        'timeout' => (int) env('TOKEN_CLEANUP_TIMEOUT', 300),
    ],
];
