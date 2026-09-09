<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class RentalAgreement extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;
    protected $fillable=[
        'agreement_id',
        'date',
        'rental_start_date',
        'rental_end_date',
        'rental_duration',
        'driver',
        'driver2',
        'vehicle',
        'terms_condition',
        'description',
        'status',
        'parent_id',
    ];

    public static $status=[
        'draft'=>'Draft',
        'pending'=>'Pending',
        'confirmed'=>'Confirmed',
        'active'=>'Active',
        'completed'=>'Completed',
        'cancelled'=>'Cancelled',
    ];

    public function drivers()
    {
        return $this->hasOne('App\Models\User', 'id', 'driver');
    }
    // public function driver2()
    // {
    //     return $this->hasOne('App\Models\Driver', 'id', 'driver2');
    // }

    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle', 'id', 'vehicle');
    }
    protected $attributes = [
        'terms_condition' => 'Your default text here'
    ];
}
