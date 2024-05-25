<?php

namespace Spatie\PdfToImage\Exceptions;

use Exception;

class PageDoesNotExist extends Exception
{
    static function forPage(int $pageNumber): self
    {
        return new static("Page {$pageNumber} does not exist.");
    }
}
