# yousign-api-php-client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Yousign REST API Client.

## Structure

```
src/
tests/
examples/
```

## Install

Via Composer

``` bash
$ composer require timothecrespy/yousign-api-php-client
```

## Usage

``` php
use TimotheCrespy\YousignClient;

$yousignClient = new YousignClient([
    'api_key' => 'YOUR_API_KEY'
]);

// If you are using Laravel (https://github.com/laravel/laravel), you could specify the default Laravel logger:
$loggerInstance = \Log::getMonolog();
$yousignClient->setLogger($loggerInstance);

$yousignClient->getUsers();
```

As the Yousign production API is not free, you might want to test it, with the staging environment:

``` php
use TimotheCrespy\YousignClient;

// Will set the API base URI to 'https://staging-api.yousign.com' instead of 'https://api.yousign.com'
$yousignClient = new YousignClient([
    'api_key' => 'YOUR_STAGING_API_KEY',
    'test' => true
]);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email contact@timothecrespy.fr instead of using the issue tracker.

## Credits

- [Timothé Crespy][link-author]
- [All Contributors][link-contributors]

### Special thanks

- [Julien Cauvin](https://github.com/jucau)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/:vendor/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/:vendor/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/:vendor/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/:vendor/:package_name.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/:vendor/:package_name
[link-travis]: https://travis-ci.org/:vendor/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/:vendor/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/:vendor/:package_name
[link-downloads]: https://packagist.org/packages/:vendor/:package_name

[link-author]: hhttps://github.com/TimotheCrespy
[link-contributors]: https://github.com/TimotheCrespy/yousign-api-php-client/graphs/contributors