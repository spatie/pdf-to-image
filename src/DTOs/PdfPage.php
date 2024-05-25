<?php

namespace Spatie\PdfToImage\DTOs;

use Spatie\PdfToImage\Enums\OutputFormat;

class PdfPage
{
    public readonly int $number;

    public readonly OutputFormat $format;

    public readonly string $prefix;

    public readonly string $path;

    public function __construct(int $pageNumber, OutputFormat $format, string $prefix, string $path)
    {
        $this->number = $pageNumber;
        $this->format = $format;
        $this->prefix = $prefix;
        $this->path = $path;
    }

    public static function make(int $pageNumber, OutputFormat $format, string $prefix, string $path): self
    {
        return new self($pageNumber, $format, $prefix, $path);
    }

    public function getFilename(): string
    {
        $info = pathinfo($this->path);

        return $info['dirname'].DIRECTORY_SEPARATOR.$this->prefix.$info['filename'].'.'.$this->format->value;
    }
}
