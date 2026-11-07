<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests\Mailables;

use Illuminate\Mail\Mailable;
use Mehedi\AmpMail\Mimes\Amp;

class NoAmpMail extends Mailable
{
    use Amp;

    public function build()
    {
        return $this
            ->subject('No amp')
            ->view('tests::html')
            ->text('tests::text')
            ->with('name', 'Mehedi Hasan');
    }
}
