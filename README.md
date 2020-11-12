# Convert a pdf to an image

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/pdf-to-image.svg?style=flat-square)](https://packagist.org/packages/spatie/pdf-to-image)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](.github/LICENSE.md)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/pdf-to-image/run-tests?label=tests)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/pdf-to-image.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/pdf-to-image)
[![StyleCI](https://styleci.io/repos/38419604/shield?branch=master)](https://styleci.io/repos/38419604)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/pdf-to-image.svg?style=flat-square)](https://packagist.org/packages/spatie/pdf-to-image)

This package provides an easy to work with class to convert pdf's to images.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/pdf-to-image.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/pdf-to-image)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Requirements

You should have [Imagick](http://php.net/manual/en/imagick.setresolution.php) and [Ghostscript](http://www.ghostscript.com/) installed. See [issues regarding Ghostscript](#issues-regarding-ghostscript).

## Installation

The package can be installed via composer:
``` bash
composer require spatie/pdf-to-image
```

## Usage

Converting a pdf to an image is easy.

```php
$pdf = new Spatie\PdfToImage\Pdf($pathToPdf);
$pdf->saveImage($pathToWhereImageShouldBeStored);
```

If the path you pass to `saveImage` has the extensions `jpg`, `jpeg`, or `png` the image will be saved in that format.
Otherwise the output will be a jpg.

## Other methods

You can get the total number of pages in the pdf:
```php
$pdf->getNumberOfPages(); //returns an int
```

By default the first page of the pdf will be rendered. If you want to render another page you can do so:
```php
$pdf->setPage(2)
    ->saveImage($pathToWhereImageShouldBeStored); //saves the second page
```

You can override the output format:
```php
$pdf->setOutputFormat('png')
    ->saveImage($pathToWhereImageShouldBeStored); //the output wil be a png, no matter what
```

You can set the quality of compression from 0 to 100:
```php
$pdf->setCompressionQuality(100); // sets the compression quality to maximum
```

## Issues regarding Ghostscript

This package uses Ghostscript through Imagick. For this to work Ghostscripts `gs` command should be accessible from the PHP process. For the PHP CLI process (e.g. Laravel's asynchronous jobs, commands, etc...) this is usually already the case. 

However for PHP on FPM (e.g. when running this package "in the browser") you might run into the following problem:

```
Uncaught ImagickException: FailedToExecuteCommand 'gs'
```

This can be fixed by adding the following line at the end of your `php-fpm.conf` file and restarting PHP FPM. If you're unsure where the `php-fpm.conf` file is located you can check `phpinfo()`. If you are using Laravel Valet the `php-fpm.conf` file will be located in the `/usr/local/etc/php/YOUR-PHP-VERSION` directory.

```
env[PATH] = /usr/local/bin:/usr/bin:/bin
```

This will instruct PHP FPM to look for the `gs` binary in the right places.

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/spatie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](.github/LICENSE.md) for more information.
