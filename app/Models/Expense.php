<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Expense extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;
    protected $fillable=[
        'title',
        'vehicle',
        'type',
        'date',
        'amount',
        'receipt',
        'notes',
        'parent_id',
    ];

    public function vehicles()
    {
        return $this->hasOne('App\Models\Vehicle','id','vehicle');
    }
    public function types()
    {
        return $this->hasOne('App\Models\ExpenseType','id','type');
    }
}
