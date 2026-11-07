<img src="https://banners.beyondco.de/Laravel%20AMP%20Mail.png?theme=light&packageManager=composer+require&packageName=mehedimi%2Flaravel-amp-mail&pattern=architect&style=style_2&description=Send+AMP+mail+through+your+Laravel+app&md=1&showWatermark=0&fontSize=100px&images=mail&widths=400&heights=400"/>

# Laravel AMP Mail

[![Tests](https://github.com/mehedimi/laravel-amp-mail/actions/workflows/tests.yml/badge.svg)](https://github.com/mehedimi/laravel-amp-mail/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehedimi/laravel-amp-mail.svg)](https://packagist.org/packages/mehedimi/laravel-amp-mail)
[![Total Downloads](https://img.shields.io/packagist/dt/mehedimi/laravel-amp-mail.svg)](https://packagist.org/packages/mehedimi/laravel-amp-mail)
[![License](https://img.shields.io/packagist/l/mehedimi/laravel-amp-mail.svg)](https://packagist.org/packages/mehedimi/laravel-amp-mail)

Send [AMP for Email](https://amp.dev/about/email/) messages through your Laravel application. Add a `text/x-amp-html` part to any mailable with a single `amp()` call.

## Requirements

- PHP 8.3+
- Laravel 11, 12 or 13

## Installation

```bash
composer require mehedimi/laravel-amp-mail
```

## Usage

Add the `Mehedi\AmpMail\Mimes\Amp` trait to your mailable and pass the amp view with the `amp` method:

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

    public function build()
    {
        return $this
            ->amp('emails.welcome-amp', ['token' => 'amp-token'])
            ->view('emails.welcome-html')
            ->text('emails.welcome-text')
            ->with('name', 'Mehedi Hasan');
    }
}
```

The views are rendered like any other mail view:

```blade
{{-- resources/views/emails/welcome-amp.blade.php --}}
<html amp4emails>
<head>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
</head>
<body>
    <p>Hello {{ $name }}! Your token is {{ $token }}.</p>
</body>
</html>
```

### Data

Data passed to `amp()` is merged into the mailable's view data, so the amp view can also use everything passed with `with()`:

```php
return $this
    ->amp('emails.welcome-amp', ['token' => 'amp-token']) // token is only set for the amp view
    ->view('emails.welcome-html')
    ->with('name', 'Mehedi Hasan');                       // name is available everywhere
```

### Queued mailables

The trait does not register any closures on the mailable, so mailables using it stay serializable and can be queued as usual:

```php
WelcomeEmail::dispatch();          // mail queue
WelcomeEmail::dispatch()->delay(now()->addMinutes(5));
```

### Attachments and embedded images

Attachments and inline images (`embed`, `attach`, ...) work as expected — the amp part always stays inside the alternative part of the message:

```
multipart/mixed
|-- multipart/alternative
|   |-- text/plain
|   |-- text/html
|   `-- text/x-amp-html
`-- invoice.pdf
```

## How it works

Gmail only renders an AMP part when it is the last part of a `multipart/alternative` structure that also contains `text/plain` and `text/html`. When the message is built, the package restructures the message body into exactly that shape:

```
multipart/alternative
|-- text/plain
|-- text/html
`-- text/x-amp-html
```

For the best results always provide all three views (`view()`, `text()` and `amp()`), and follow the [AMP for Email specification](https://github.com/ampproject/amphtml/blob/main/extensions/amp-email/README.md) in the amp view.

> **Note:** Gmail only displays AMP emails from senders that are registered with Google, over TLS with SPF/DKIM in place. See the [Gmail AMP for Email documentation](https://developers.google.com/gmail/ampemail) for the registration steps.

## Testing your mailables

The message can be inspected like any other mailable. For example, to assert that the amp part exists:

```php
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;

Mail::fake();

Mail::to('user@example.com')->send(new WelcomeEmail);

Mail::assertSent(function (WelcomeEmail $mail) {
    return $mail->ampView === 'emails.welcome-amp';
});
```

Or, using the array mailer to inspect the raw mime message:

```php
$raw = Mail::mailer('array')
    ->to('user@example.com')
    ->send(new WelcomeEmail)
    ->getSymfonySentMessage()
    ->toString();

$this->assertStringContainsString('text/x-amp-html', $raw);
```

## Upgrading from 2.x

Version 3.0 drops the SwiftMailer integration that was removed from Laravel 9 and uses the Symfony Mailer APIs:

- `text/x-amp-html` is now placed inside `multipart/alternative` as required by Gmail (2.x appended the part with `Swift_Message::addPart`).
- Mailables using the trait can now be queued — 2.x registered a closure that broke serialization.
- The trait requires Laravel 11+ and PHP 8.3+.
- The protected `addAmpPart()` method now receives the `Symfony\Component\Mime\Email` instance.

## Notes

- The trait overrides the protected `buildAttachments()` hook of the mailable to restructure the message body. If your own mailable defines a `buildAttachments()` method, the amp part will not be added — avoid overriding it in mailables that use this trait.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mehedihasansabbirmi@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
