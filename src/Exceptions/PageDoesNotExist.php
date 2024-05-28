<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class PageDoesNotExist extends Exception
{
    public static function for(int $pageNumber): self
    {
        return new static("Page {$pageNumber} does not exist.");
    }
}
