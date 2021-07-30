<img src="https://banners.beyondco.de/Laravel%20AMP%20Mail.png?theme=light&packageManager=composer+require&packageName=mehedimi%2Flaravel-amp-mail&pattern=architect&style=style_2&description=Send+AMP+mail+through+your+Laravel+app&md=1&showWatermark=0&fontSize=100px&images=mail&widths=400&heights=400"/>

# Laravel AMP Mail
Send amp mail through your Laravel app

## Installation
```bash
composer require mehedimi/laravel-amp-mail
```

## Quick Usages
To send amp email just use `Mehedi\AmpMail\Mimes\Amp` trait on your mail class and load amp view by using `amp` method.
### Example 
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mehedi\AmpMail\Mimes\Amp;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels, Amp;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->amp('amp')
            ->view('html')
            ->with('name', 'Mehedi Hasan');
    }
}
```
