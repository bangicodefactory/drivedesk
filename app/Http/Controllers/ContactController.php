<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

/**
 * The storefront's /contact page (BAN-333).
 *
 * It replaces `Route::view('/contact', 'client.pages.contact')`, whose entire
 * body was the sentence "This is a placeholder contact page. Replace with real
 * content." on a URL that is in sitemap.xml and linked from the storefront
 * header, the footer and the booking confirmation.
 *
 * The form only exists where it can actually deliver. The tenant's contact
 * address is a Setting an owner fills in (Settings → General), and if it is
 * empty there is nowhere to send a message to -- so the page renders its
 * channels (phone, WhatsApp, address, hours) and no form at all, rather than
 * accepting a message and dropping it. That is the failure mode the newsletter
 * endpoint still has, and it is not worth reproducing here.
 */
class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('Public/Contact', [
            // The page hides the form on false. It is a separate prop rather
            // than something derived from the `contact` shared prop, because
            // `contact.email` is the address shown to visitors as a mailto:
            // link and this is the address messages are delivered to -- today
            // the same Setting, but the page should not have to assume that.
            'canSendMessage' => $this->recipient() !== null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'email'     => 'required|email|max:160',
            'phone'     => 'nullable|string|max:40',
            // Optional, and only ever a hint for whoever reads the message --
            // it is not resolved to a booking_requests row, so a wrong or
            // invented reference tells an attacker nothing about whether it
            // exists (booking_requests carries a name, email and phone).
            'reference' => 'nullable|string|max:40',
            'message'   => 'required|string|max:2000',
        ]);

        $recipient = $this->recipient();

        if ($recipient === null) {
            // Reachable only by posting directly, since index() hides the form
            // in this case. Never report success: that is the lie this whole
            // controller exists to avoid.
            Log::warning('ContactController: a message arrived but no company_email is configured; nothing was sent.');

            return back()->with('error', __('Sending is unavailable right now. Please call or message us instead.'));
        }

        Mail::to($recipient)->send(new ContactMessage([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'reference' => $data['reference'] ?? null,
            'message'   => $data['message'],
        ]));

        return back()->with('success', __('Thanks — your message has been sent. We will reply shortly.'));
    }

    /** The tenant's own contact address, or null when an owner never set one. */
    private function recipient(): ?string
    {
        try {
            $email = settings()['company_email'] ?? null;
        } catch (\Throwable) {
            // settings() reads the DB; a storefront that cannot reach it should
            // still render the page rather than 500 on a public URL.
            return null;
        }

        $email = is_string($email) ? trim($email) : '';

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
