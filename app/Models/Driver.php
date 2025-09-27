<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
// Include signature handling traits and contracts
use Creagia\LaravelSignPad\Concerns\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;

class Driver extends Authenticatable implements CanBeSigned
{
    use HasFactory,Notifiable, RequiresSignature;

    protected $fillable=[
        'driver_id',
        'user_id',
        'gender',
        'age',
        'address',
        'birth_date',
        'license_number',
        'issue_date',
        'expiration_date',
        'document',
        'license',
        'reference',
        'parent_id',
        'notes',
        'document_1',
        'license_1',
        'ICE_company'

    ];
    // public function getSignatureRoute(): string
    // {
    //     // Implement the method to return the signature route
    //     return route('signature.route', ['driver' => $this->id]);
    // }

    // public function hasBeenSigned(): bool
    // {
    //     // Implement the method to check if the driver has been signed
    //     return !is_null($this->signature);
    // }
}
