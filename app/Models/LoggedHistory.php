<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LoggedHistory extends Model
{
    use HasFactory, MassPrunable;
    public $fillable=[
        'user_id',
        'ip',
        'date',
        'details',
        'type',
        'parent_id',
    ];

    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }

    /**
     * F-19 (perf-audit): the activity log grows unbounded. Prune entries older
     * than the configured retention window (default 365 days) via the scheduled
     * `model:prune` command (App\Console\Kernel). MassPrunable issues a chunked
     * DELETE — no model events, nothing loaded into memory.
     */
    public function prunable()
    {
        $days = (int) config('audit.logged_history_retention_days', 365);

        return static::where('created_at', '<=', now()->subDays($days));
    }
}
