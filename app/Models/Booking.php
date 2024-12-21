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
    ];

    public static $status = [
        'yet_to_start' => 'Yet to Start',
        'completed' => 'Completed',
        'on_going' => 'On Going',
        'cancelled' => 'Cancelled',
    ];

    public static $paymentStatus = [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'partial_paid' => 'Partial Paid',
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
         $vehicle_details=!empty($this->vehicle_details)?json_decode($this->vehicle_details):[];
         return $vehicle_details;
    }
}
