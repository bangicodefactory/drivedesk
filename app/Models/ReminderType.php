<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parent_id',
        
    ];
        // protected $table = 'reminder_types'; 

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}
