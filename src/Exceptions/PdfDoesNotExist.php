<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class PdfDoesNotExist extends Exception
{
    public static function for(string $pdfFile): self
    {
        return new static("File '{$pdfFile}' does not exist.");
    }
}
