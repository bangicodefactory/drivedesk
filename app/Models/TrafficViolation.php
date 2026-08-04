<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A traffic violation notice (contravention / PV) issued against one of the
 * fleet's vehicles, matched back to the booking and renter that held the
 * vehicle at `occurred_at`. See App\Services\ViolationMatcher for the matching
 * rules and BAN-260 for the feature.
 */
class TrafficViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'reference',
        'authority',
        'license_plate',
        'occurred_at',
        'notice_date',
        'location',
        'description',
        'amount',
        'vehicle_id',
        'booking_id',
        'driver_user_id',
        'match_confidence',
        'match_source',
        'matched_at',
        'confirmed_by',
        'confirmed_at',
        'status',
        'liable_party',
        'amount_recovered',
        'document',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'occurred_at'      => 'datetime',
        'notice_date'      => 'date',
        'matched_at'       => 'datetime',
        'confirmed_at'     => 'datetime',
        'amount'           => 'decimal:2',
        'amount_recovered' => 'decimal:2',
    ];

    /** How confident the matcher is that `booking_id` is the right rental. */
    public const CONFIDENCE_EXACT    = 'exact';
    public const CONFIDENCE_PROBABLE = 'probable';
    public const CONFIDENCE_NONE     = 'none';

    /** Who produced the current match. */
    public const SOURCE_AUTO   = 'auto';
    public const SOURCE_MANUAL = 'manual';

    /** Recovery workflow. Marked by hand — the app sends nothing itself (BAN-260 scope). */
    public static $statuses = [
        'new'         => 'New',
        'notified'    => 'Renter Notified',
        'paid'        => 'Paid',
        'contested'   => 'Contested',
        'written_off' => 'Written Off',
    ];

    /** Who is on the hook for the amount. */
    public static $liableParties = [
        'unknown' => 'Unknown',
        'renter'  => 'Renter',
        'company' => 'Company',
    ];

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_id');
    }

    public function booking()
    {
        return $this->hasOne(Booking::class, 'id', 'booking_id');
    }

    /** The renter, snapshotted at match time. `bookings.driver` holds a users.id. */
    public function driver()
    {
        return $this->hasOne(User::class, 'id', 'driver_user_id');
    }

    /** True once a booking is attached, however it got there. */
    public function isMatched(): bool
    {
        return $this->booking_id !== null;
    }
}
