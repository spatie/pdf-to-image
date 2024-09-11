<?php

use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

it('will throw an exception when trying to convert a non existing file', function () {
    new Pdf('pdf-does-not-exist.pdf');
})->throws(PdfDoesNotExist::class);

it('will throw an exception when passed an invalid page number', function ($invalidPage) {
    (new Pdf($this->testFile))->selectPage($invalidPage);
})
    ->throws(PageDoesNotExist::class)
    ->with([100, 0, -1]);

it('will correctly return the number of pages in a pdf file', function () {
    $pdf = new Pdf($this->multipageTestFile);

    expect($pdf->pageCount())->toEqual(3);
});

it('will accept a custom specified resolution', function ($resolution) {
    $image = (new Pdf($this->testFile))
        ->resolution($resolution)
        ->getImageData('test.jpg', 1)
        ->getImageResolution();

    expect($image['x'])->toEqual($resolution);
    expect($image['y'])->toEqual($resolution);
})
    ->with([127, 16]);

it('can select a single page', function () {
    $imagick = (new Pdf($this->testFile))
        ->selectPage(1)
        ->getImageData('page-1.jpg', 1);

    expect($imagick)->toBeInstanceOf(Imagick::class);
});

it('can select multiple pages', function () {
    $pdf = (new Pdf($this->multipageTestFile))
        ->selectPages(1, 3);

    $imagick1 = $pdf
        ->getImageData('page-1.jpg', 1);

    expect($imagick1)->toBeInstanceOf(Imagick::class);
});

it('can set the background color', function ($backgroundColor) {
    $image = (new Pdf($this->testFile))
        ->backgroundColor($backgroundColor)
        ->selectPage(1)
        ->getImageData('page-1.jpg', 1)
        ->getImageBackgroundColor()
        ->getColorAsString();

    $expectedSRGBValueForWhiteColor = 'srgb(255,255,255)';
    expect($image)->toEqual($expectedSRGBValueForWhiteColor);
})->with(['srgb(255,255,255)', 'rgb(255,255,255)', 'white', '#fff']);
