# Contributing

Thanks for considering to contribute to Laravel AMP Mail!

## Development setup

```bash
git clone git@github.com:mehedimi/laravel-amp-mail.git
cd laravel-amp-mail
composer install
```

## Running the tests

The package is tested with [Orchestra Testbench](https://github.com/orchestral/testbench) against Laravel 11, 12 and 13:

```bash
composer test
```

To run the tests against a specific Laravel version:

```bash
# Laravel 12 (testbench 10.*)
composer update --with laravel/framework:^12.0 --with orchestra/testbench:^10.0
composer test
```

CI runs the full PHP 8.3–8.5 × Laravel 11–13 matrix on every push and pull request.

## Sending a pull request

- Add or update tests for any behaviour change.
- Keep the code style consistent with the existing codebase.
- Update the `README.md` / `CHANGELOG.md` when the public behaviour changes.
- New features require a changelog entry and a test.
