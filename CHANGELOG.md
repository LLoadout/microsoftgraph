# Changelog

All notable changes to `microsoftgraph` will be documented in this file.

## v1.22 - 2026-11-10

- More robust OAuth callback handling: an error from Microsoft, a missing authorization code, or a code that could not be exchanged for an access token (for example one already redeemed by a link-preview bot) now dispatches `MicrosoftGraphErrorReceived` instead of failing on an empty token response.
- Refreshing an expired access token with an invalid refresh token now throws a clear "please reconnect" exception instead of dispatching an event with empty data.

## v1.21 - 2026-10-22

- Added support for Laravel 13 (and 11, 12) with `orchestra/testbench` 9–11.
- Minimum PHP version is now 8.2.
- Fixed the `onedrive` disk on Laravel 13: the `Storage::extend()` closure is rebound by the framework, so the provider is now captured explicitly.
- `OnedriveAdapter` now matches the Flysystem 3 interface signatures and throws `UnableToReadFile` instead of returning `false` from `read()`.
- `MicrosoftGraphTransport::send()` uses an explicit nullable `Envelope` (PHP 8.4 deprecation).
- Switched from the abandoned `nunomaduro/larastan` to `larastan/larastan`.
