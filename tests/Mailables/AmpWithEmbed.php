<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests\Mailables;

use Illuminate\Mail\Mailable;
use Mehedi\AmpMail\Mimes\Amp;
use Symfony\Component\Mime\Email;

class AmpWithEmbed extends Mailable
{
    use Amp;

    public function build()
    {
        $this
            ->subject('With embed')
            ->amp('tests::amp')
            ->view('tests::embed-html')
            ->text('tests::text')
            ->with('name', 'Mehedi Hasan');

        $this->withSymfonyMessage(function (Email $message) {
            $message->embed('IMGDATA', 'pic.png');
        });

        return $this;
    }
}
