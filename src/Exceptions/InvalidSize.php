<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class InvalidSize extends Exception
{
    public static function for(int $value, string $type, string $property): static
    {
        return new static(ucfirst($type)." {$property} must be greater than or equal to 0, {$value} given.");
    }

    public static function forThumbnail(int $value, string $property): static
    {
        return static::for($value, 'thumbnail', $property);
    }

    public static function forImage(int $value, string $property): static
    {
        return static::for($value, 'image', $property);
    }
}
