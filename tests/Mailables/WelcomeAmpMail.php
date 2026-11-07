<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Mehedi\AmpMail\Mimes\Amp;

class WelcomeAmpMail extends Mailable
{
    use Amp, Queueable;

    public function build()
    {
        return $this
            ->subject('Welcome')
            ->amp('tests::amp', ['extra' => 'amp-data'])
            ->view('tests::html')
            ->text('tests::text')
            ->with('name', 'Mehedi Hasan');
    }
}
