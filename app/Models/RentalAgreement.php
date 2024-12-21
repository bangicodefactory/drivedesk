<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalAgreement extends Model
{
    use HasFactory;
    protected $fillable=[
        'agreement_id',
        'date',
        'rental_start_date',
        'rental_end_date',
        'rental_duration',
        'driver',
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

    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle', 'id', 'vehicle');
    }
}
