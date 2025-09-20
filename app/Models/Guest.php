<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'type',
        'profile',
        'phone_number',
        'lang',
        'subscription',
        'subscription_expire_date',
        'parent_id',
        'company_name',
        'city',
        'is_active',
    ];

    public function bookings()
    {
        return $this->hasMany(BookingRequest::class);
    }
}