<?php

namespace Spatie\PdfToImage\Exceptions;

class InvalidSize extends \Exception
{
    public static function for(int $value, string $type, string $property): self
    {
        return new static(ucfirst($type) . " {$property} must be greater than or equal to 0, {$value} given.");
    }

    public static function forThumbnail(int $value, string $property): self
    {
        return self::for($value, 'thumbnail', $property);
    }

    public static function forImage(int $value, string $property): self
    {
        return self::for($value, 'image', $property);
    }
}
