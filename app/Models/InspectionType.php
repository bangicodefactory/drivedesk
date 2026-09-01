<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class InspectionType extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;

    protected $fillable=[
        'type',
        'parent_id',
    ];
}
