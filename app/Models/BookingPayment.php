<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'date',
        'payment_method',
        'notes',
        'parent_id',
    ];

    public static $paymentMethod = [
        'Wire Transfer' => 'Wire Transfer',
        'Card' => 'Card',
    ];
}
