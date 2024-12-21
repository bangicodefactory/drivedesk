<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'billing_type',
        'parent_id',
    ];

    public static $billingType = [
        'daily' => 'Daily',
        'total' => 'Total',
    ];
}
