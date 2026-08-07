<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class PasswordNotSupported extends Exception
{
    public static function forImageMagickMajorVersion(int $majorVersion): static
    {
        return new static("Opening password-protected PDFs requires ImageMagick 7, but ImageMagick {$majorVersion} is installed. ImageMagick 6 ignores the password, so the PDF would fail to open anyway.");
    }
}
