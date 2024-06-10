<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class InvalidQuality extends Exception
{
    public static function for(int $value): static
    {
        return new static("Quality must be between 1 and 100, {$value} given.");
    }
}
