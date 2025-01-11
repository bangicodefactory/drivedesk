<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_vehicle',
        'parent_id',
        'name',
        'note',
        'reminder_date',
        'status',
        'reminder_type_id'
    ];

    public function reminderType(): BelongsTo
    {
        return $this->belongsTo(ReminderType::class);
    }
    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle','id','vehicle');
    }
    public function users()
    {
        return $this->hasOne('App\Models\User','id','inspector');
    }
}
