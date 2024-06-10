<?php

use Spatie\PdfToImage\Pdf;

afterEach(function () {
    $this->unlinkAllOutputImages($this->outputDirectory);
});

it('saves a page to an image', function () {
    $targetFilename = $this->outputDirectory.'/test.jpg';

    (new Pdf($this->testFile))
        ->selectPage(1)
        ->save($targetFilename);

    expect(file_exists($targetFilename))->toBeTrue();
});

it('saves only selected pages to images', function () {
    (new Pdf($this->multipageTestFile))
        ->selectPages(1, 3)
        ->format(\Spatie\PdfToImage\Enums\OutputFormat::Png)
        ->save($this->outputDirectory);

    foreach ([1, 3] as $pageNumber) {
        expect(file_exists($this->outputDirectory.'/'.$pageNumber.'.png'))->toBeTrue();
    }
});

it('saves all pages as images', function () {
    (new Pdf($this->multipageTestFile))
        ->format(\Spatie\PdfToImage\Enums\OutputFormat::Jpg)
        ->saveAllPages($this->outputDirectory);

    foreach (range(1, 3) as $pageNumber) {
        expect(file_exists($this->outputDirectory.'/'.$pageNumber.'.jpg'))->toBeTrue();
    }
});
