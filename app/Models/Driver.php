<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable=[
        'driver_id',
        'user_id',
        'gender',
        'age',
        'address',
        'birth_date',
        'license_number',
        'issue_date',
        'expiration_date',
        'document',
        'license',
        'reference',
        'parent_id',
        'notes',

    ];
}
