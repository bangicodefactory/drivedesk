<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A message sent from the storefront's /contact form to the tenant's own
 * contact address (Settings → General, "company_email").
 *
 * Sent synchronously rather than queued, for the same reason as DemoRequest:
 * these deployments run on shared hosting with QUEUE_CONNECTION=sync, and a
 * queued mail there is a mail nobody ever delivers.
 *
 * replyTo is the visitor, so staff can answer straight from their inbox. The
 * From address stays the application's own — sending as the visitor would be
 * a forged sender and lands the message in spam at best.
 */
class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{name:string,email:string,phone:?string,reference:?string,message:string} $data */
    public function __construct(public array $data)
    {
    }

    public function build()
    {
        $subject = $this->data['reference']
            ? __('Message from :name — booking :reference', [
                'name'      => $this->data['name'],
                'reference' => $this->data['reference'],
            ])
            : __('Message from :name', ['name' => $this->data['name']]);

        return $this
            ->subject($subject)
            ->replyTo($this->data['email'], $this->data['name'])
            ->markdown('email.contact_message', ['data' => $this->data]);
    }
}
