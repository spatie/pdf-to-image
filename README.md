<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=pdf-to-image">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/pdf-to-image/html/dark.webp">
        <img alt="Logo for pdf-to-image" src="https://spatie.be/packages/header/pdf-to-image/html/light.webp">
      </picture>
    </a>

<h1>Convert a PDF to an image</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/pdf-to-image.svg?style=flat-square)](https://packagist.org/packages/spatie/pdf-to-image)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/pdf-to-image.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/pdf-to-image)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/pdf-to-image.svg?style=flat-square)](https://packagist.org/packages/spatie/pdf-to-image)
    
</div>

This package provides an easy-to-work-with class to convert a PDF to one or more image.

## Requirements

You should have [Imagick](http://php.net/manual/en/imagick.setresolution.php) and [Ghostscript](http://www.ghostscript.com/) installed. 
See [issues regarding Ghostscript](#issues-regarding-ghostscript) and [Imagick Issues](#imagick-issues) for more information.

## Installation

The package can be installed via composer and requires PHP 8.2+:

```bash
composer require spatie/pdf-to-image
```

> If you are using PHP < 8.2, use version 2.0 of this package.

## Usage

Converting a PDF to an image is easy.

```php
$pdf = new \Spatie\PdfToImage\Pdf($pathToPdf);
$pdf->save($pathToWhereImageShouldBeStored);
```

If the filename you pass to `saveImage` has the extensions `jpg`, `jpeg`, `png`, or `webp` the image will be saved in that format; otherwise the output format will be `jpg`.

The `save()` method returns an array with the filenames of the saved images if multiple images are saved, otherwise returns a string with the path to the saved image.

## Other methods

Get the total number of pages in the pdf:

```php
/** @var int $numberOfPages */
$numberOfPages = $pdf->pageCount();
```

Check if a file type is a supported output format:

```php
/** @var bool $isSupported */
$isSupported = $pdf->isValidOutputFormat('jpg');
```

By default, only the first page of the PDF will be rendered. To render another page, call the `selectPage()` method:

```php
$pdf->selectPage(2)
    ->save($pathToWhereImageShouldBeStored); //saves the second page
```

Or, select multiple pages with the `selectPages()` method:

```php
$pdf->selectPages(2, 4, 5)
    ->save($directoryToWhereImageShouldBeStored); //saves the 2nd, 4th and 5th pages
```

Change the output format:

```php
$pdf->format(\Spatie\PdfToImage\Enums\OutputFormat::Webp)
    ->save($pathToWhereImageShouldBeStored); //the saved image will be in webp format
```

Set the output quality _(the compression quality)_ from 0 to 100:

```php
$pdf->quality(90) // set an output quality of 90%
    ->save($pathToWhereImageShouldBeStored);
```

Set the output resolution DPI:

```php
$pdf->resolution(300) // resolution of 300 dpi
    ->save($pathToWhereImageShouldBeStored);
```

Specify the thumbnail size of the output image:

```php
$pdf
   ->thumbnailSize(400) // set thumbnail width to 400px; height is calculated automatically
   ->save($pathToWhereImageShouldBeStored);

// or:
$pdf
   ->thumbnailSize(400, 300) // set thumbnail width to 400px and the height to 300px
   ->save($pathToWhereImageShouldBeStored);
```

Set the output image width:

```php
$pdf->size(400) // set the width to 400px; height is calculated automatically
    ->save($pathToWhereImageShouldBeStored);
```

Set the output image width and height:

```php
$pdf->size(400, 300) // set the width to 400px and the height to 300px
    ->save($pathToWhereImageShouldBeStored);
```

Get the dimensions of the PDF. This can be used to determine if the PDF is extremely high-resolution.

```php
/** @var \Spatie\PdfToImage\DTOs\PageSize $size */
$size = $pdf->getSize();

$width = $size->width;
$height = $size->height;
```

> [!NOTE]
> $directoryToWhereImagesShouldBeStored must be an existing directory

Save all pages to images:

```php
$pdf->saveAllPages($directoryToWhereImagesShouldBeStored);
```

Set the Merge Layer Method for Imagick:

```php
$pdf->layerMethod(\Spatie\PdfToImage\Enums\LayerMethod::Merge);

// or disable layer merging:
$pdf->layerMethod(\Spatie\PdfToImage\Enums\LayerMethod::None);
```

Set the background color of the output image:

```php
$pdf->backgroundColor('white') // simple text for 'white' color
    ->save($pathToWhereImageShouldBeStored);

$pdf->backgroundColor('#fff') // code for 'white' color
    ->save($pathToWhereImageShouldBeStored);

$pdf->backgroundColor('rgb(255,255,255)') // rgb for 'white' color
    ->save($pathToWhereImageShouldBeStored);
```

Convert a password-protected (encrypted) PDF by passing the password with the `password()` method:

```php
$pdf->password('your-password')
    ->save($pathToWhereImageShouldBeStored);
```

This requires ImageMagick 7. ImageMagick 6 reads the password from an internal struct field that the `imagick` extension cannot write to, so it silently ignores any password it is given. On ImageMagick 6, `password()` therefore throws a `PasswordNotSupported` exception rather than failing later with an unrelated "Failed to read the file" error.

You can check which behaviour applies to your installation:

```php
Pdf::supportsPasswordProtectedPdfs(); // true on ImageMagick 7
```

Not every password can be used. ImageMagick hands the password to Ghostscript through a command line, and replaces every character outside of a fixed allowlist with an underscore first. Ghostscript then reports `Password did not work.` even though the password is correct. On Linux and macOS the allowlist holds the ASCII letters, the digits, the space and ``$-_.+!;*(),{}|\^~[]`"><#%/?:@&=``. Notably, a single quote and any non-ASCII character (such as `é` or `密`) are not supported. On Windows a single quote is supported, but a double quote and a backslash are not.

`password()` throws a `PasswordContainsUnsupportedCharacters` exception when the given password holds such a character. There is no length limit: a password of any length works, as long as every character is supported.

The full list of supported characters for your platform is available through:

```php
Pdf::supportedPasswordCharacters();
```

## Ultra-wide PDFs

When working with ultra-wide PDFs, you may encounter issues loading PDF files. To resolve this, update your `policy.xml` Imagick file, using values appropriate for your use case:

```xml
  <policy domain="resource" name="width" value="4GiB"/>
  <policy domain="resource" name="height" value="4GiB"/>
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

## Imagick Issues

If you receive an error with the message `attempt to perform an operation not allowed by the security policy 'PDF'`, you may need to add the following line to your `policy.xml` file. This file is usually located in `/etc/ImageMagick-[VERSION]/policy.xml`, such as `/etc/ImageMagick-7/policy.xml`.

```xml
<policy domain="coder" rights="read | write" pattern="PDF" />
```

## Testing

`spatie/pdf-to-image` uses the PEST framework for unit tests. They can be run with the following command:

``` bash
./vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Patrick Organ](https://github.com/patinthehat)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
