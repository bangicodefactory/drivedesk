<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'vehicle',
        'driver',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'pickup_address',
        'drop_off_address',
        'status',
        'amount',
        'payment_status',
        'payment_notes',
        'notes',
        'addon',
        'details',
        'vehicle_details',
        'parent_id',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class,'driver');
    }

// In BookingRequest.php
public function car()
{
    return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle', 'id');
}

    public function pickupPlace()
    {
        return $this->belongsTo(Place::class, 'pickup_address');
    }

    public function dropOffPlace()
    {
        return $this->belongsTo(Place::class, 'drop_off_address');
    }
    public function getTotalAmount()
    {
        return $this->amount;
    }
}
