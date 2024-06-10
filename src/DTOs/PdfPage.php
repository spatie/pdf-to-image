<?php

namespace Spatie\PdfToImage\DTOs;

use Spatie\PdfToImage\Enums\OutputFormat;

class PdfPage
{
    public function __construct(
        public int $number,
        public OutputFormat $format,
        public string $prefix,
        public string $path
    ) {
        //
    }

    public static function make(int $pageNumber, OutputFormat $format, string $prefix, string $path): self
    {
        return new self($pageNumber, $format, $prefix, $path);
    }

    public function filename(): string
    {
        $info = pathinfo($this->path);

        return $info['dirname'].DIRECTORY_SEPARATOR.$this->prefix.$info['filename'].'.'.$this->format->value;
    }
}
