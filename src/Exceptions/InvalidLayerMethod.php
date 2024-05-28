<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class InvalidLayerMethod extends Exception
{
    public static function for(int $value)
    {
        return new static("Invalid layer method value ({$value}).");
    }
}
