<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests\Mailables;

use Illuminate\Mail\Mailable;
use Mehedi\AmpMail\Mimes\Amp;

class HtmlOnlyAmpMail extends Mailable
{
    use Amp;

    public function build()
    {
        return $this
            ->subject('Html only')
            ->amp('tests::amp')
            ->view('tests::html')
            ->with('name', 'Mehedi Hasan');
    }
}
