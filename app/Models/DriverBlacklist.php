<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * A driver (keyed by the driver's users.id) flagged for a tenant, with the
 * reason. A null `lifted_at` means currently blacklisted; lifting keeps the row.
 * `overrides` is an append-only audit of "proceed anyway" decisions (BAN-252).
 */
class DriverBlacklist extends Model
{
    protected $fillable = [
        'driver_user_id',
        'parent_id',
        'reason',
        'blacklisted_by',
        'lifted_at',
        'lifted_by',
        'overrides',
    ];

    protected $casts = [
        'lifted_at' => 'datetime',
        'overrides' => 'array',
    ];

    public function driverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    /** Currently-active blacklist rows (not yet lifted). */
    public function scopeActive($query)
    {
        return $query->whereNull('lifted_at');
    }

    /**
     * Append a "proceed anyway" override to the audit (BAN-252): who proceeded,
     * when, on which booking/contract, for which driver, and the reason at the time.
     */
    public function recordOverride(string $contextType, int $contextId, int $driverUserId): void
    {
        $this->overrides = array_merge($this->overrides ?? [], [[
            'by_user_id'      => Auth::id(),
            'by_user_name'    => optional(Auth::user())->name,
            'at'              => now()->toDateTimeString(),
            'context_type'    => $contextType,
            'context_id'      => $contextId,
            'driver_user_id'  => $driverUserId,
            'reason_snapshot' => $this->reason,
        ]]);
        $this->save();
    }

    /**
     * Active blacklist rows for a set of driver user ids within a tenant,
     * keyed by driver_user_id — for batch lookups on list/picker pages.
     */
    public static function activeFor(array $userIds, int $parentId): Collection
    {
        if (empty($userIds)) {
            return collect();
        }

        return static::query()
            ->where('parent_id', $parentId)
            ->whereIn('driver_user_id', $userIds)
            ->whereNull('lifted_at')
            ->get()
            ->keyBy('driver_user_id');
    }
}
