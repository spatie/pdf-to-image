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
    ->with([127, 150, 16]);

it('will convert a specified page', function () {
    $imagick = (new Pdf($this->multipageTestFile))
        ->selectPage(2)
        ->getImageData('page-2.jpg', 2);

    expect($imagick)->toBeInstanceOf(Imagick::class);
});

it('will select multiple pages', function () {
    $pdf = (new Pdf($this->multipageTestFile))
        ->selectPages(1, 3);

    $imagick1 = $pdf
        ->getImageData('page-1.jpg', 1);

    $imagick2 = $pdf
        ->getImageData('page-3.jpg', 3);

    expect($imagick1)->toBeInstanceOf(Imagick::class);
    expect($imagick2)->toBeInstanceOf(Imagick::class);
});
