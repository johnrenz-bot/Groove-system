<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAgreementPdf extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfData;
    public $coach;
    public $client_name;

    public function __construct($pdfData, $coach, $client_name)
    {
        $this->pdfData = $pdfData;
        $this->coach = $coach;
        $this->client_name = $client_name;
    }

public function build()
{
    return $this->subject('Your Agreement Contract')
                ->view('emails.agreement_email') // must match the file path
                ->attachData($this->pdfData, "Agreement_{$this->coach->firstname}_{$this->coach->lastname}.pdf");
}


}
