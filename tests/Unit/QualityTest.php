<?php

use Spatie\PdfToImage\Pdf;

it('will throw an exception when passed an invalid quality value', function ($quality) {
    (new Pdf($this->testFile))->quality($quality);
})
    ->throws(\Spatie\PdfToImage\Exceptions\InvalidQuality::class)
    ->with([-1, 0, 101]);

it('will set output quality', function () {
    $imagick = (new Pdf($this->testFile))
        ->quality(99)
        ->getImageData('test.jpg', 1);

    expect($imagick->getCompressionQuality())->toEqual(99);
});
