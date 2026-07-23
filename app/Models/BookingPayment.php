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
        'invoice_days',
        'parent_id',
    ];

    // Only invoice_days is cast; `date` is intentionally left uncast so it
    // stays a raw string for the facture builders.
    protected $casts = [
        'invoice_days' => 'integer',
    ];

    public static $paymentMethod = [
        'Espece' => 'Espece',
        'Virement bancaire' => 'Virement bancaire',
        'Carte' => 'Carte',
        'Chèque' => 'Chèque',
    ];
}
