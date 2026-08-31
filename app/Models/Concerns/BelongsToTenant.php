<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Tenant isolation (roadmap Tranche S.1).
 *
 * Multiple owners share one database — `parent_id` is the tenant boundary, and
 * `HomeController` reports `User::where('type','owner')->count()` to the
 * super-admin as "totalOrganization". Read paths applied that boundary by hand;
 * most write paths did not, so a permission alone was enough to reach another
 * tenant's row by id. This trait applies it to every query on the model instead
 * of relying on each action to remember.
 *
 * Three cases deliberately bypass the scope:
 *
 * 1. **No authenticated user.** Console commands, seeders and queue jobs run
 *    outside a request. `parentId()` dereferences `Auth::user()` and would
 *    fatal, and there is no tenant to scope to anyway (`DevDataSeeder` creates
 *    bookings this way).
 * 2. **Super admins.** `parentId()` returns the *caller's own id* for a super
 *    admin, which is never any tenant's `parent_id` — scoping on it would hide
 *    every row in the system from them.
 * 3. **An explicit opt-out**, `Model::acrossTenants()`, for legitimate
 *    cross-tenant reads. Named so it is greppable and obvious in review. No
 *    production code needs it yet — `ViolationMatcher` and the seeders already
 *    pass an explicit `parent_id` or run unauthenticated — so today it is used
 *    only by tests that assert a foreign row still exists.
 *
 * **Do not apply this trait to the auth provider model** (`App\Models\User`,
 * per `config/auth.php`). The scope calls `Auth::check()`, which resolves the
 * guard, which calls `retrieveById()`, which queries that model again — through
 * this same scope. `SessionGuard::user()` has no re-entrancy guard, so it
 * recurses without bound rather than failing safe. Where a user row needs
 * constraining, scope the lookup at the call site instead (see
 * `DriverController`, BAN-291).
 *
 * @see docs/product-roadmap.md — Tranche S.1
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (! static::tenantScopeApplies()) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.parent_id', parentId());
        });

        // A row created inside a request belongs to the caller's tenant unless
        // it says otherwise, so the scope can find it again afterwards.
        static::creating(function ($model) {
            // is_null, not empty(): `parent_id` is NOT NULL default 0 and
            // BookingFactory sets 0 deliberately, so empty() would silently rewrite
            // an intentionally out-of-tenant fixture to the caller's tenant and mask
            // a real isolation failure.
            if (is_null($model->parent_id) && static::tenantScopeApplies()) {
                $model->parent_id = parentId();
            }
        });
    }

    /** Whether the current context should be constrained to one tenant. */
    protected static function tenantScopeApplies(): bool
    {
        // Auth::check() rather than Auth::hasUser(): hasUser() reports only an
        // already-resolved user, so on the first model query of a request it
        // would answer false and silently skip the scope. check() resolves.
        if (! Auth::check()) {
            return false;                       // console, seeder, queue
        }

        return Auth::user()->type !== 'super admin';
    }

    /**
     * Drop the tenant constraint for one query. Use only where reading across
     * tenants is the actual intent, and say why at the call site.
     */
    public static function acrossTenants(): Builder
    {
        return static::query()->withoutGlobalScope('tenant');
    }
}
