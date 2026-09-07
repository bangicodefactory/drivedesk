<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'phone_number',
        'profile',
        'lang',
        'parent_id',
        'is_active',
        'company_name',
        'city',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function totalUser()
    {
        return User::whereNotIn('type', ['driver'])->where('parent_id', $this->id)->count();
    }


    public function totalDriver()
    {
        return User::where('type', 'driver')->where('parent_id', $this->id)->count();
    }


    public function roleWiseUserCount($role)
    {
        return User::where('type', $role)->where('parent_id', parentId())->count();
    }

    public static function getDevice($user)
    {
        $mobileType = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tabletType = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobileType, $user)) {
            return 'mobile';
        } else {
            if (preg_match_all($tabletType, $user)) {
                return 'tablet';
            } else {
                return 'desktop';
            }

        }
    }


    public static $gender = [
        'Male' => 'Male',
        'Female' => 'Female',
    ];

    public function drivers()
    {
        return $this->hasOne('App\Models\Driver', 'user_id', 'id');
    }

    public static $systemModules = [
        'user',
        'driver',
        'vehicle',
        'inspection',
        'booking',
        'planning',
        'expense',
        'rental agreement',
        'logged history',
        'settings',
        'reminder',
        'tva',
    ];

    public function languageKeyword()
    {
        __('Daily');
        __('Total');
        __('Automatic');
        __('Manual');
        __('Essence');
        __('Diesel');
        __('This deployment already has an owner.');
    }

    /**
     * Whether this deployment already has its business owner (BAN-307).
     *
     * DriveDesk ships one deployment per business owner: own database, own
     * domain, sharing nothing. `parentId()` returns an owner's *own* id, so a
     * second owner is a second tenant living inside one customer's database --
     * invisible to them, counted in their dashboard totals, and the thing that
     * makes the `acrossTenants()` writes in generateMonthlyTva() and
     * TvaRenumberService genuinely dangerous rather than merely unscoped.
     *
     * Deliberately not a model-level guard. Tests and seeders legitimately build
     * multi-owner fixtures to prove the tenant scope still separates them; the
     * invariant is about what the *application* lets a user do, so it is
     * enforced at the four request paths that can produce an owner.
     *
     * Unscoped on purpose: there is no tenant above an owner to scope to, and
     * User carries no global scope (see BelongsToTenant -- applying one to the
     * auth provider model recurses without bound).
     */
    public static function ownerExists(): bool
    {
        return static::where('type', 'owner')->exists();
    }
}
