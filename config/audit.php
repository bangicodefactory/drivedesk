<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activity-log retention
    |--------------------------------------------------------------------------
    |
    | How many days of `logged_histories` (activity-log) rows to keep before
    | the scheduled `model:prune` deletes them — prevents the table growing
    | unbounded (perf-audit F-19). Override per deploy with the env var.
    |
    */

    'logged_history_retention_days' => env('LOGGED_HISTORY_RETENTION_DAYS', 365),

];
