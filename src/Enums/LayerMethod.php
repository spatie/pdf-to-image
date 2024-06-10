<?php

namespace Spatie\PdfToImage\Enums;

use Imagick;

enum LayerMethod: int
{
    case None = -1;
    case Undefined = Imagick::LAYERMETHOD_UNDEFINED;
    case Coalesce = Imagick::LAYERMETHOD_COALESCE;
    case CompareAny = Imagick::LAYERMETHOD_COMPAREANY;
    case CompareClear = Imagick::LAYERMETHOD_COMPARECLEAR;
    case CompareOverlay = Imagick::LAYERMETHOD_COMPAREOVERLAY;
    case Dispose = Imagick::LAYERMETHOD_DISPOSE;
    case Optimize = Imagick::LAYERMETHOD_OPTIMIZE;
    case OptimizePlus = Imagick::LAYERMETHOD_OPTIMIZEPLUS;
    case OptimizeTrans = Imagick::LAYERMETHOD_OPTIMIZETRANS;
    case Composite = Imagick::LAYERMETHOD_COMPOSITE;
    case OptimizeImage = Imagick::LAYERMETHOD_OPTIMIZEIMAGE;
    case RemoveDups = Imagick::LAYERMETHOD_REMOVEDUPS;
    case RemoveZero = Imagick::LAYERMETHOD_REMOVEZERO;
    case Merge = Imagick::LAYERMETHOD_MERGE;
    case Flatten = Imagick::LAYERMETHOD_FLATTEN;
    case Mosaic = Imagick::LAYERMETHOD_MOSAIC;
    case TrimBounds = Imagick::LAYERMETHOD_TRIMBOUNDS;

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) instanceof self;
    }
}
