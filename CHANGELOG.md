# Changelog

All notable changes to `microsoftgraph` will be documented in this file.

## Unreleased

- Added support for Laravel 13 (and 11, 12) with `orchestra/testbench` 9–11.
- Minimum PHP version is now 8.2.
- Fixed the `onedrive` disk on Laravel 13: the `Storage::extend()` closure is rebound by the framework, so the provider is now captured explicitly.
- `OnedriveAdapter` now matches the Flysystem 3 interface signatures and throws `UnableToReadFile` instead of returning `false` from `read()`.
- `MicrosoftGraphTransport::send()` uses an explicit nullable `Envelope` (PHP 8.4 deprecation).
- Switched from the abandoned `nunomaduro/larastan` to `larastan/larastan`.
