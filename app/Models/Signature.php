<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    // No BelongsToTenant: the signatures table has no parent_id column, so there
    // is nothing for the global scope to constrain — adding one needs a migration
    // and a backfill, which is why this model stays excluded.
    //
    // An earlier revision of this comment claimed a signature is 'reached through
    // its driver, which is tenant-scoped'. That was wrong, and it would have
    // buried the gap: index() listed every tenant's signatures and destroy() took
    // an unscoped bound model. Both are constrained through user_id in the
    // controller now (BAN-297) — the only tenant link this table has.
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
