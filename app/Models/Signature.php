<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    // No BelongsToTenant: the signatures table has no parent_id column, so
    // there is nothing to scope on. A signature is reached through its driver,
    // which is tenant-scoped, and SignatureController's user_id rule is
    // tenant-checked (BAN-296). Adding a parent_id here would need a migration.
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
