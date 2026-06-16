<?php

namespace App\Http\Controllers;

use App\Mail\DemoRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoRequestController extends Controller
{
    /**
     * Where demo requests are delivered. Central inbox for the product team.
     */
    private const RECIPIENT = 'admin@bangicode.ma';

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'company' => 'required|string|max:160',
            'email'   => 'required|email|max:160',
            'phone'   => 'nullable|string|max:40',
            'message' => 'nullable|string|max:2000',
        ]);

        Mail::to(self::RECIPIENT)->send(new DemoRequest($data));

        // Persist the request as a *pending* workspace login a super-admin can
        // later approve (BAN-249). The schema is frozen (§4), so there is no
        // demo_requests table: a pending request IS an inactive `manager`
        // sub-user of the demo tenant. Approval flips is_active + emails a
        // set-password link; decline deletes the row. (DemoApprovalController.)
        $this->provisionPendingDemoUser($data);

        return back()->with('success', __('Thanks! Your demo request has been sent — we\'ll be in touch shortly.'));
    }

    /**
     * Create the pending (inactive, unverified) demo user under the demo tenant.
     * Best-effort and idempotent — never blocks the request or the admin email.
     */
    private function provisionPendingDemoUser(array $data): void
    {
        // The demo tenant owner (one per demo deployment). If there isn't one
        // yet, skip silently — the product team still got the email above.
        $owner = User::where('type', 'owner')->first();
        if (! $owner) {
            return;
        }

        // users.email is unique and a prospect may submit twice — never create a
        // second row for the same address.
        if (User::where('email', $data['email'])->exists()) {
            return;
        }

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            // Unknown to anyone; the real password is set via the link emailed on
            // approval, so this random value is never used to log in.
            'password'     => Hash::make(Str::random(40)),
            'type'         => 'manager',
            'parent_id'    => $owner->id,
            'company_name' => $data['company'],
            'phone_number' => $data['phone'] ?? null,
            'profile'      => 'avatar.png',
            'lang'         => 'english',
            // Pending: both guards in AuthenticatedSessionController (is_active +
            // email_verified_at) keep the account un-loggable until approval.
            'is_active'    => 0,
        ]);

        $managerRole = Role::where('name', 'manager')->where('parent_id', $owner->id)->first();
        if ($managerRole) {
            $user->assignRole($managerRole);
        }
    }
}
