<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Credit extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'amount',
        'status',
        'credit_date',
        'parent_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credit_date' => 'date',
    ];

    public const STATUS_NON_PAYE = 'non payé';
    public const STATUS_PAYE = 'payé';

    public static $statuses = [
        'non payé' => 'non payé',
        'payé' => 'payé',
    ];

    /**
     * Driver (user) that owns the credit.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }
}
