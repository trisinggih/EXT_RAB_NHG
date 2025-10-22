<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Tes Kirim Email via Gmail SMTP';

    public function build()
    {
        return $this->view('emails.test')
                    ->with(['name' => 'Tri Prasetyo Singgih']);
    }
}
