<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CallbackMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $user = Auth::user();

        $this->data = $data;
        $this->subject = 'Обратная связь от пользователя "'.$user->name.'" Калькуляции';
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
            ->cc([
                'zolotarev@ktbbeton.com', 'a.parfenov@ktbbeton.com'
            ])
            ->with([
                'data' => $this->data
            ])
            ->subject($this->subject)
            ->view('emails.callback');
    }
}
