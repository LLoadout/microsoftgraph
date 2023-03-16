# Laravel package for using Microsoft Graph API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lloadout/microsoftgraph.svg?style=flat-square)](https://packagist.org/packages/lloadout/microsoftgraph)
[![Total Downloads](https://img.shields.io/packagist/dt/lloadout/microsoftgraph.svg?style=flat-square)](https://packagist.org/packages/lloadout/microsoftgraph)

This package makes it possible to send e-mail with Microsoft, use Microsoft Onedrive and send messages via Microsoft Teams, all via the Microsoft Graph API

## Installation

You can install the package via composer:

```bash
composer require lloadout/microsoftgraph
```

add this to your .env file and fill it with the values you specified in Microsoft Azure Portal app registration.
You have to provide this API permissions: for mail: `Mail.send`, for files: `Files.ReadWrite.all`, for Teams Chat: `Chat.ReadWrite`.

```
MS_TENANT_ID=
MS_CLIENT_ID=
MS_CLIENT_SECRET=
MS_GRAPH_API_VERSION=v1.0
MS_REDIRECT_URL=
```

## Mail usage

```php
Mail::send(new YourMailable());

Mail::raw('The body of my first test message', function($message) {
    $message->to('john@doe.com', 'John Doe')->subject('A mail send via lloadout/microsoftgraph');
});
```

## Storage usage

The package created a disk called `onedrive`

```php
$disk = Storage::disk('onedrive');
#create a dir
$disk->makeDirectory('Test folder');
#storing files
$disk->put('Test folder/file1.txt','Content of file 1');
$disk->put('Test folder/file2.txt','Content of file 2');
#getting files
Storage::disk('onedrive')->get('Test folder/file1.txt');
```

## Teams usage

```php 
Teams::send('your-channel-id','Hello world!');
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

The callback will fire an MicrosoftGraphCallbackReceived event, this will automatically store your token in the session.  You can add your token store logic in a listener for this event, for example:

```
Event::listen(function (MicrosoftGraphCallbackReceived $event) {
    $user = Auth::user();
    $user->accessdata = $event->accessData;
    $user->save();
});
```

The package will search for a session variable name `microsoftgraph-access-data` for establishing the connection.  So please provide this variable with your accessData as value when logging in.
For example:  On login, you get your accesData from the database and store it into the session variable `microsoftgraph-access-data`.

## If you want to send mail with the package then do this additional steps:

Set the environment variable MAIL_MAILER in your .env file

```
MAIL_MAILER=microsoftgraph
```

note: make sure your from address is the address you gave the consent to

## If you want to use the storage driver then do this additional steps:

add the onedrive root to your .env file:

```
MS_ONEDRIVE_ROOT="me/drive/root"
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
