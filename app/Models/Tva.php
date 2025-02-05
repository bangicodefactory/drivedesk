<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tva extends Model
{
    use HasFactory;
    protected $fillable = [
        'month',
        'year',
        'total_amount',
        'tva_amount',
        'status',
        'generated_date'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
