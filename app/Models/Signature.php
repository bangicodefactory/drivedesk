<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\BelongsToTenant;

class Signature extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = ['user_id', 'signature_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getSignatureUrlAttribute()
    {
        return Storage::url($this->signature_path);
    }
}
