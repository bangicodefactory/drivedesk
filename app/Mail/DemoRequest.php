<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * "Book a demo" submission from the demo-gateway landing, delivered to the
 * product team. Sent synchronously (not ShouldQueue) so a contact form on a
 * shared host with QUEUE_CONNECTION=sync delivers immediately.
 */
class DemoRequest extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{name:string,company:string,email:string,phone:?string,message:?string} $data */
    public function __construct(public array $data)
    {
    }

    public function build()
    {
        return $this
            ->subject('New demo request — ' . $this->data['company'])
            ->replyTo($this->data['email'], $this->data['name'])
            ->markdown('email.demo_request', ['data' => $this->data]);
    }
}
