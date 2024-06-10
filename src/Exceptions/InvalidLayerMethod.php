<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class InvalidLayerMethod extends Exception
{
    public static function for(int $value): static
    {
        return new static("Invalid layer method value ({$value}).");
    }
}
