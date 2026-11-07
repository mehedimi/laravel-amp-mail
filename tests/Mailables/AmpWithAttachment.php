<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests\Mailables;

use Illuminate\Mail\Mailable;
use Mehedi\AmpMail\Mimes\Amp;

class AmpWithAttachment extends Mailable
{
    use Amp;

    public function build()
    {
        return $this
            ->subject('With attachment')
            ->amp('tests::amp')
            ->view('tests::html')
            ->text('tests::text')
            ->with('name', 'Mehedi Hasan')
            ->attachData('PDFDATA', 'file.pdf', ['mime' => 'application/pdf']);
    }
}
