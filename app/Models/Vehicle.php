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

