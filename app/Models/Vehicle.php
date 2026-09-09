<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's vehicle cannot resolve by id.
    use BelongsToTenant;

    use HasFactory;
    protected $fillable=[
        'vehicle_id',
        'type',
        'name',
        'model',
        'engine_type',
        'engine_no',
        'registration_expiry_date',
        'license_plate',
        'document',
        'daily_rate',
        'year_of_ﬁrst_immatriculation',
        'gearbox',
        'fuel_type',
        'number_of_seats',
        'kilometers',
        'option',
        'notes',
        'parent_id',
        'available_for_rent',
    ];

    protected $casts = [
        'available_for_rent' => 'boolean',
    ];

    /**
     * The vehicle's first-registration year.
     *
     * The column is spelled with a U+FB01 LATIN SMALL LIGATURE FI --
     * "year_of_&#xfb01;rst_immatriculation" -- which arrived with the original
     * schema and cannot be renamed (CLAUDE.md §8: no rename-in-place). Every
     * caller that spelled it with a plain "fi" therefore read null and showed
     * "N/A": the car detail page, and the vehicle_details snapshot written onto
     * every booking request, which has been storing "year": null since it was
     * added.
     *
     * Read through this accessor rather than by attribute name. It is not in
     * $appends on purpose -- Vehicle is serialised on a lot of screens, and
     * this only needs to appear where it is asked for (->append(...)).
     */
    public function getFirstRegistrationYearAttribute()
    {
        return $this->attributes["year_of_ﬁrst_immatriculation"] ?? null;
    }

    public function types()
    {
        return $this->hasOne('App\Models\VehicleType','id','type');
    }

    /**
     * Canonical form of a license plate for storage and de-duplication.
     *
     * Collapses every run of whitespace — including unicode separators like the
     * non-breaking space (U+00A0) that plain trim() leaves behind — to a single
     * space, then trims. Imports pasted from Excel/web often carry NBSPs, which
     * otherwise let a visually-identical plate slip past the unique index and
     * the duplicate guard and create a second vehicle (IST-229).
     */
    public static function normalizePlate($plate): string
    {
        $out = preg_replace('/[\p{Z}\s]+/u', ' ', (string) $plate);
        return trim($out ?? (string) $plate);
    }

    /** Lowercased normalized plate — the key used for case-insensitive matching. */
    public static function plateKey($plate): string
    {
        return mb_strtolower(self::normalizePlate($plate));
    }

    public static $gearbox=[
        'automatic'=>'Automatic',
        'manual'=>'Manual'
    ];

    public static $fuelType=[
        'essence'=>'Essence',
        'diesel'=>'Diesel',
        'petrol'=>'Petrol',
        'hybrid'=>'Hybrid',
        'electric'=>'Electric',
        'gas'=>'Gas',


    ];

    public function options()
    {
        if(!empty($this->option)){
            $options=explode(',',$this->option);
            return Option::whereIn('id',$options)->get();
        }
    }
}

