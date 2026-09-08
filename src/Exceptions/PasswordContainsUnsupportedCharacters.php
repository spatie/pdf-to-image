<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class PasswordContainsUnsupportedCharacters extends Exception
{
    public static function for(string $supportedCharacters): static
    {
        return new static("The password contains characters that ImageMagick does not hand over to Ghostscript. It replaces them with an underscore, after which Ghostscript reports 'Password did not work.' even though the password is correct. Only these characters are supported: {$supportedCharacters}");
    }
}
