<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'city',
        'island',
        'price',
        'parent_id',
        'depo_name',
        'depo_address',
    ];
}
