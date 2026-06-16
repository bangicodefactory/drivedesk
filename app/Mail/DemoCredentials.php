<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to a prospect when their demo request is approved (BAN-249). Carries a
 * branded *set-password* link (a Breeze reset token), never a plaintext
 * password. Sync (not ShouldQueue), like DemoRequest, so it delivers reliably on
 * a QUEUE_CONNECTION=sync host.
 */
class DemoCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    public string $appName;

    public function __construct(public User $user, string $token)
    {
        // A standard Breeze reset link — the prospect sets their own password and
        // is then a normal (active, verified) workspace user.
        $this->url = route('password.reset', ['token' => $token, 'email' => $user->email]);
        // Prefer the client's canonical product name (config/clients/<client>.php)
        // over the APP_NAME env, so the email reads "DriveDesk" regardless of how
        // the deploy set APP_NAME.
        $this->appName = config('client.name') ?: config('app.name', 'DriveDesk');
    }

    public function build()
    {
        return $this
            ->subject('Your ' . $this->appName . ' demo is ready')
            ->markdown('email.demo_credentials', [
                'user'    => $this->user,
                'url'     => $this->url,
                'appName' => $this->appName,
            ]);
    }
}
