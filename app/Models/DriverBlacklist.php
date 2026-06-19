<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

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
