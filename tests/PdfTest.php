<?php

use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

function unlinkAllOutputImages(string $path) {
    $files = glob($path . '/*');

    foreach ($files as $file) {
        if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'gitignore') {
            unlink($file);
        }
    }
}

beforeEach(function () {
    $this->testFile = __DIR__.'/files/test.pdf';
    $this->multipageTestFile = __DIR__.'/files/multipage-test.pdf';
});

afterEach(function () {
    unlinkAllOutputImages(__DIR__.'/output');
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

it('checks if the provided output format string is a supported format', function ($formats, $expected) {
    $pdf = (new Pdf($this->testFile));

    foreach($formats as $format) {
        expect($pdf->isValidOutputFormat($format))->toBe($expected);
    }
})
->with([
    'supported formats' => [['jpg', 'png', 'webp'], true],
    'unsupported formats' => [['bmp', 'gif', ''], false],
]);

it('saves a page to an image', function() {
    $targetFilename = __DIR__.'/output/test.jpg';

    (new Pdf($this->testFile))
        ->selectPage(1)
        ->saveImage($targetFilename);

    expect(file_exists($targetFilename))->toBeTrue();
});

it('saves only selected pages to images', function() {
    $targetPath = __DIR__.'/output';

    (new Pdf($this->multipageTestFile))
        ->selectPages(1, 3)
        ->format(\Spatie\PdfToImage\Enums\OutputFormat::Png)
        ->saveImage($targetPath);

    foreach ([1, 3] as $pageNumber) {
        expect(file_exists($targetPath . '/' . $pageNumber . '.png'))->toBeTrue();
    }
});

it('saves all pages to images', function() {
    $targetPath = __DIR__.'/output';

    (new Pdf($this->multipageTestFile))
        ->format(\Spatie\PdfToImage\Enums\OutputFormat::Jpg)
        ->saveAllPagesAsImages($targetPath);

    foreach (range(1, 3) as $pageNumber) {
        expect(file_exists($targetPath . '/' . $pageNumber . '.jpg'))->toBeTrue();
    }
});
