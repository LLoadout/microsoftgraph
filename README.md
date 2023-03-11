# Laravel package for using Microsoft Graph API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lloadout/microsoftgraph.svg?style=flat-square)](https://packagist.org/packages/lloadout/microsoftgraph)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lloadout/microsoftgraph/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lloadout/microsoftgraph/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lloadout/microsoftgraph.svg?style=flat-square)](https://packagist.org/packages/lloadout/microsoftgraph)

This package makes it possible to send e-mail via the Microsoft Graph API

## Installation

You can install the package via composer:

```bash
composer require lloadout/microsoftgraph
```

add this to your .env file and fill it with the values you specified in Microsoft Azure Portal app registration

```
MS_TENANT_ID=
MS_CLIENT_ID=
MS_CLIENT_SECRET=
MS_GRAPH_API_VERSION=v1.0
MS_REDIRECT_URL=
```

## Connect your account

The package provides two oAuth routes

The first redirects you to the consent screen of Microsoft

```
https://your-url.com/microsoft/connect
```

The second is the callback url you need to specify in Microsoft Azure Portal app registration as redirect uri

```
https://your-url.com/microsoft/callback
```

The callback will fire an MicrosoftGraphCallbackReceived event, you can add your token store logic in a listener for this event, for example:

```
Event::listen(function (MicrosoftGraphCallbackReceived $event) {
    $user = Auth::user();
    $user->token = $event->user['token'];
    $user->save();
});
```

The package will search for a session variable name `microsoftgraph-token` for establishing the connection.  So please provide this variable

## If you want to send mail with the package then do this additional steps:

Set the environment variable MAIL_MAILER in your .env file

```
MAIL_MAILER=microsoftgraph
```

## Usage

```php
Mail::send(new YourMailable());
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dieter Coopman](https://github.com/LLoadout)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
