# Laravel AMP Mail
Added support for amp mail

## Installation
```bash
$ composer require mehedimi/laravel-amp-mail
```

## Quick Usages
To send amp email just use `Mehedi\Mimes\Amp` trait on your mail class and load amp view by using `amp` method.
### Example 
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mehedi\Mimes\Amp;

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
