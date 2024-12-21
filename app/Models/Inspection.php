<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;
    protected $fillable=[
        'vehicle',
        'meter_reading_outgoing',
        'meter_reading_incoming',
        'outgoing_date',
        'outgoing_time',
        'incoming_date',
        'incoming_time',
        'details',
        'notes',
        'parent_id',
        'status',
        'inspector',
        'inspection_date',
    ];

    public static $status=[
        'pending'=>'Pending',
        'completed'=>'Completed',
        'in_progress'=>'In Progress',
        'reject'=>'Reject',
        'conditional_pass'=>'Conditional Pass',
        'on_hold'=>'On Hold',
    ];

    public static  $repairStatus=[
        'needs_repair'=>'Needs Repair',
        'pending'=>'Pending',
        'completed'=>'Completed',
        'in_progress'=>'In Progress',
        'on_hold'=>'On Hold',
    ];

    public static $fuelLevel=[
        '1/4'=>' 1/4',
        '1/2'=>' 1/2',
        '3/4'=>' 3/4',
        'Full Tank'=>'Full Tank',
    ];

    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle','id','vehicle');
    }
    public function users()
    {
        return $this->hasOne('App\Models\User','id','inspector');
    }

}
