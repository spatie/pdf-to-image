<?php

use Spatie\PdfToImage\Pdf;

afterEach(function () {
    $this->unlinkAllOutputImages($this->outputDirectory);
});

it('saves a page to an image', function () {
    $targetFilename = $this->outputDirectory.'/test.jpg';

    (new Pdf($this->testFile))
        ->selectPage(1)
        ->saveImage($targetFilename);

    expect(file_exists($targetFilename))->toBeTrue();
});

it('saves only selected pages to images', function () {
    (new Pdf($this->multipageTestFile))
        ->selectPages(1, 3)
        ->format(\Spatie\PdfToImage\Enums\OutputFormat::Png)
        ->saveImage($this->outputDirectory);

    foreach ([1, 3] as $pageNumber) {
        expect(file_exists($this->outputDirectory.'/'.$pageNumber.'.png'))->toBeTrue();
    }
});

it('saves all pages to images', function () {
    (new Pdf($this->multipageTestFile))
        ->format(\Spatie\PdfToImage\Enums\OutputFormat::Jpg)
        ->saveAllPagesAsImages($this->outputDirectory);

    foreach (range(1, 3) as $pageNumber) {
        expect(file_exists($this->outputDirectory.'/'.$pageNumber.'.jpg'))->toBeTrue();
    }
});
