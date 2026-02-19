<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
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
        'parent_id',
        'addon',
        'details',
        'notes',
        'vehicle_details',
        'discount',
        'daily_price_final',
    ];

    protected $casts = [
        'details' => 'object',
        // Store vehicle snapshot as associative array for easy access
        'vehicle_details' => 'array',
    ];

    public static $status = [
        'yet_to_start' => 'Yet to Start',
        'completed' => 'Completed',
        'on_going' => 'On Going',
        'cancelled' => 'Cancelled',
    ];

    public static $paymentStatus = [
        'impaye' => 'impayé',
        'paye' => 'Payé',
        'partiellement_paye' => 'partiellement payé',
    ];

    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client');
    }

    public function drivers()
    {
        return $this->hasOne('App\Models\User', 'id', 'driver');
    }

    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle', 'id', 'vehicle');
    }

    public function pickupAddress()
    {
        return $this->hasOne('App\Models\Place', 'id', 'pickup_address');
    }
    public function dropOffAddress()
    {
        return $this->hasOne('App\Models\Place', 'id', 'drop_off_address');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\BookingPayment', 'booking_id', 'id');
    }
    public function getTotalAmount()
    {
        return $this->amount;
    }

    public function getTotalDueAmount()
    {
        $bookingDueAmount = 0;
        foreach ($this->payments as $bookingPayment) {
            $bookingDueAmount += $bookingPayment->amount;
        }
        return $this->getTotalAmount() - $bookingDueAmount;
    }
    public static function statusChange($booking_id, $status)
    {
        $booking = Booking::find($booking_id);
        $booking->payment_status = $status;
        $booking->save();
        return $booking;
    }

    public function addons()
    {
        $addons=!empty($this->addon)?explode(',',$this->addon):[];
        return Addon::whereIn('id',$addons)->get();
    }

    public function vehicleDetails()
    {
        $data = $this->vehicle_details;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }
        if (is_object($data)) {
            return $data; // already object (unlikely with array cast)
        }
        if (is_array($data)) {
            return (object)$data;
        }
        return (object)[];
    }
}
