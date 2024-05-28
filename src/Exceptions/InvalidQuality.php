<?php

namespace Spatie\PdfToImage\Exceptions;

class InvalidQuality extends \Exception
{
    public static function for(int $value): self
    {
        return new static("Quality must be between 1 and 100, {$value} given.");
    }
}
