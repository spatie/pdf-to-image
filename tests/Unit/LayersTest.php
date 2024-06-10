<?php

use Spatie\PdfToImage\Exceptions\InvalidLayerMethod;
use Spatie\PdfToImage\Pdf;

it('can accept a layer', function () {
    $image = (new Pdf($this->testFile))
        ->layerMethod(\Spatie\PdfToImage\Enums\LayerMethod::None)
        ->resolution(72)
        ->getImageData('test.jpg', 1)
        ->getImageResolution();

    expect($image['x'])->toEqual(72);
    expect($image['y'])->toEqual(72);
});

it('throws an error when passed an invalid layer method', function () {
    (new Pdf($this->testFile))->layerMethod(-100);
})
    ->throws(InvalidLayerMethod::class);
