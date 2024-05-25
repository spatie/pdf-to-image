<?php

namespace Spatie\PdfToImage\Enums;

enum OutputFormat: string
{
    case Jpg = 'jpg';
    case Jpeg = 'jpeg';
    case Png = 'png';
    case Webp = 'webp';
}
