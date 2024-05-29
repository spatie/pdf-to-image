<?php

namespace Spatie\PdfToImage\DTOs;

class PageSize
{
    public function __construct(
        public int $width,
        public int $height,
    ) {
        //
    }

    public static function make(int $width, int $height): self
    {
        return new self($width, $height);
    }
}
