<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

/**
 * Ownership checks for bookings (roadmap Tranche S.1).
 *
 * The `BelongsToTenant` global scope is the primary defence — it stops another
 * tenant's booking from ever resolving, so route-model binding 404s before a
 * controller runs. This policy is the explicit backstop for the cases the scope
 * cannot cover: a query that deliberately opted out via `acrossTenants()`, an
 * id fetched through a relation, or a future action that hands a model in from
 * somewhere else.
 *
 * Permission (`can('edit booking')`) answers *what* a user may do; this answers
 * *which rows* they may do it to.
 *
 * Note that `$model->save()` and `->delete()` go through `newModelQuery()`,
 * which does **not** apply global scopes — so a model obtained via
 * `acrossTenants()` can still be written across tenants. That is the gap this
 * policy exists to close, and it only closes it where a caller invokes it.
 */
class BookingPolicy
{
    /**
     * Super admins operate across tenants by design — the dashboard reports on
     * every organisation — so they are allowed through before any check below.
     */
    public function before(User $user): ?bool
    {
        return $user->type === 'super admin' ? true : null;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $this->ownsIt($user, $booking);
    }

    public function update(User $user, Booking $booking): bool
    {
        return $this->ownsIt($user, $booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $this->ownsIt($user, $booking);
    }

    /**
     * The booking must sit in the caller's tenant. `parentId()` resolves to the
     * owner's own id for an owner, and to `parent_id` for their staff, so both
     * roles compare against the same value.
     */
    private function ownsIt(User $user, Booking $booking): bool
    {
        return (int) $booking->parent_id === (int) parentId();
    }
}
