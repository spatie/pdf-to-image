<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class PdfDoesNotExist extends Exception
{
    public static function forFile(string $pdfFile): self
    {
        return new static("File '{$pdfFile}' does not exist.");
    }
}
