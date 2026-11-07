# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2026-11-07

### Changed
- Require PHP 8.3+ and Laravel 11, 12 or 13.
- Use the Symfony Mailer APIs instead of SwiftMailer, which was removed in Laravel 9.

### Fixed
- The `text/x-amp-html` part is now placed as the last part of `multipart/alternative`, the structure required by Gmail to render AMP emails. Previously the part was appended next to the alternative part, which Gmail ignores.
- Mailables using the `Amp` trait can be queued again: the trait no longer registers a closure on the mailable, which previously broke mailable serialization.
- Attachments and embedded images no longer interfere with the amp part.

### Removed
- Support for Laravel 10 and below.

## [2.0.0] - 2021-09-06

- Renamed the package to `mehedimi/laravel-amp-mail`.
- The trait moved to the `Mehedi\AmpMail\Mimes` namespace.

## [1.0.0] - 2021

- Initial release: `amp()` method for Laravel mailables via a SwiftMailer part.
