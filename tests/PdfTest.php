<?php

use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

beforeEach(function () {
    $this->testFile = __DIR__.'/files/test.pdf';
    $this->multipageTestFile = __DIR__.'/files/multipage-test.pdf';
    $this->remoteFileUrl = 'https://tcd.blackboard.com/webapps/dur-browserCheck-BBLEARN/samples/sample.pdf';
});

it('will throw an exception when try to convert a non existing file', function () {
    new Pdf('pdf-does-not-exist.pdf');
})->throws(PdfDoesNotExist::class);

it('will throw an exception when passed an invalid page number', function ($invalidPage) {
    (new Pdf($this->testFile))->selectPage($invalidPage);
})
->throws(PageDoesNotExist::class)
->with([100, 0, -1]);

it('will correctly return the number of pages in pdf file', function () {
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
->with([127, 150, 166]);

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

it('will accept an output format and convert to it', function ($format) {
    $fmt = \Spatie\PdfToImage\Enums\OutputFormat::from($format);

    $imagick = (new Pdf($this->testFile))
        ->format($fmt)
        ->getImageData('test.' . $fmt->value, 1);

    expect($imagick->getFormat())->toEqual($fmt->value);
})
->with(['jpg', 'png', 'webp']);

it('can accept a layer', function () {
    $image = (new Pdf($this->testFile))
        ->mergeLayerMethod(Imagick::LAYERMETHOD_FLATTEN)
        ->resolution(72)
        ->getImageData('test.jpg', 1)
        ->getImageResolution();

    expect($image['x'])->toEqual(72);
    expect($image['y'])->toEqual(72);
});

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

it('will create a thumbnail at specified width', function () {
    $imagick = (new Pdf($this->multipageTestFile))
       ->thumbnailWidth(400)
       ->getImageData('test.jpg', 1)
       ->getImageGeometry();

    expect($imagick['width'])->toBe(400);
});
