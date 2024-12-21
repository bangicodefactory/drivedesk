<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
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
    ];

    public function types()
    {
        return $this->hasOne('App\Models\VehicleType','id','type');
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

