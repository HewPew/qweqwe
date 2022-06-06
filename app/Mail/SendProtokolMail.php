<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class SendProtokolMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->subject = isset($data['subject']) ? $data['subject'] : 'Уведомление от Калькуляции';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('zapros@ktbbeton.com', 'Калькуляция')
            ->to($this->data['to'])
            ->with([
                'data' => $this->data
            ])
            ->subject($this->subject)
            //->attach(storage_path("app/" . $this->data['file']))
            ->view('emails.notify');
    }
}
