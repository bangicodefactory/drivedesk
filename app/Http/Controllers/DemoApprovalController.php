<?php

namespace App\Http\Controllers;

use App\Mail\DemoCredentials;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

/**
 * Super-admin actions on pending demo requests (BAN-249).
 *
 * A "pending demo request" has no table of its own (schema is frozen, §4): it is
 * an inactive `manager` sub-user of the demo tenant, created by
 * DemoRequestController when the prospect submits the landing form.
 *
 *   approve → activate + verify the user, email a branded set-password link.
 *   decline → delete the pending row.
 *
 * Routes are guarded by `auth` + `feature:demo_gateway`; super-admin-only and the
 * pending-row invariant are enforced here so the {user} binding can't be turned
 * against a real account.
 */
class DemoApprovalController extends Controller
{
    public function approve(Request $request, User $user)
    {
        $this->guard($user);

        // Activate + verify so login's is_active / email_verified_at checks pass
        // once the prospect sets their password.
        $user->forceFill([
            'is_active'         => 1,
            'email_verified_at' => now(),
        ])->save();

        // Branded set-password link. Reuses Breeze's reset broker (a real,
        // single-use token) without touching the global reset notification, so
        // no plaintext password ever leaves the server.
        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new DemoCredentials($user, $token));

        return back()->with('success', __('Demo approved — a set-password link was sent to :email.', ['email' => $user->email]));
    }

    public function decline(Request $request, User $user)
    {
        $this->guard($user);

        $user->delete();

        return back()->with('success', __('Demo request declined.'));
    }

    /**
     * Only a super-admin may act, and only on a genuinely pending demo row
     * (inactive `manager` sub-user of the demo tenant). Anything else → 404 so
     * the {user} route binding can't reach a real account.
     */
    private function guard(User $user): void
    {
        abort_unless(Auth::user()?->type === 'super admin', 403);

        $ownerId = optional(User::where('type', 'owner')->first())->id;

        abort_unless(
            $user->type === 'manager'
                && (int) $user->is_active === 0
                && (int) $user->parent_id === (int) $ownerId,
            404
        );
    }
}
