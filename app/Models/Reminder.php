<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class Reminder extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'id_vehicle',
        'parent_id',
        'name',
        'note',
        'reminder_date',
        'status',
        'reminder_type_id',
    ];

    protected $casts = [
        'reminder_date' => 'date',
    ];

    public function reminderType(): BelongsTo
    {
        return $this->belongsTo(ReminderType::class);
    }
    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle','id','id_vehicle');
    }
    public function users()
    {
        return $this->hasOne('App\Models\User','id','inspector');
    }
    public function vehicle()
{
    return $this->belongsTo(Vehicle::class);
}
}
