<?php

namespace Spatie\PdfToImage\Exceptions;

class InvalidThumbnailSize extends \Exception
{
    public static function forWidth(int $value): self
    {
        return new static("Thumbnail width must be greater than or equal to 0, {$value} given.");
    }

    public static function forHeight(int $value): self
    {
        return new static("Thumbnail height must be greater than or equal to 0, {$value} given.");
    }
}
