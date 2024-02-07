<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppMailer extends Mailable
{
    use Queueable, SerializesModels;

    /*
     * Object that stores all of the email information such as subject, from, view etc...
     */
    public $data;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (isset($this->data->subject)) {
	    $this->subject($this->data->subject);
	}

        if (isset($this->data->attachments)) {
            foreach ($this->data->attachments as $attachment) {
                $this->attach($attachment['file'], $attachment['options']);
            }
	}

        return $this->view($this->data->view);
    }
}
