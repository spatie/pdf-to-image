<?php

namespace Spatie\PdfToImage\Enums;

use Imagick;

enum ResourceLimitType: int
{
    case Area = Imagick::RESOURCETYPE_AREA;
    case Disk = Imagick::RESOURCETYPE_DISK;
    case File = Imagick::RESOURCETYPE_FILE;
    case Map = Imagick::RESOURCETYPE_MAP;
    case Memory = Imagick::RESOURCETYPE_MEMORY;
    case Time = Imagick::RESOURCETYPE_TIME;
    case Throttle = Imagick::RESOURCETYPE_THROTTLE;
    case Thread = Imagick::RESOURCETYPE_THREAD;
}
