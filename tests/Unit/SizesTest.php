<?php

use Spatie\PdfToImage\Pdf;

it('will create a thumbnail at specified sizes', function () {
    $pdf = (new Pdf($this->multipageTestFile));

    $imagick = $pdf
        ->thumbnailSize(400)
        ->getImageData('test.jpg', 1)
        ->getImageGeometry();

    expect($imagick['width'])->toBe(400);
    expect($imagick['height'])->toBeGreaterThan(50);

    $imagick = $pdf
        ->thumbnailSize(200, 300)
        ->getImageData('test.jpg', 1)
        ->getImageGeometry();

    expect($imagick['width'])->toBe(200);
    expect($imagick['height'])->toBe(300);
});

it('will throw an exception when passed an invalid thumbnail size', function ($width, $height) {
    (new Pdf($this->testFile))->thumbnailSize($width, $height);
})
    ->throws(\Spatie\PdfToImage\Exceptions\InvalidSize::class)
    ->with([
        'invalid width' => [-1, 100],
        'invalid height' => [100, -1],
        'invalid size' => [-1, -1],
    ]);

it('will create an image at specified sizes', function () {
    $pdf = (new Pdf($this->multipageTestFile));

    $size = $pdf
        ->size(400)
        ->getImageData('test.jpg', 1)
        ->getImageGeometry();

    expect($size['width'])->toBe(400);
    expect($size['height'])->toBeGreaterThan(20);

    $size = $pdf
        ->size(200, 300)
        ->getImageData('test.jpg', 1)
        ->getImageGeometry();

    expect($size['width'])->toBe(200);
    expect($size['height'])->toBe(300);
});

it('will throw an exception when passed an invalid image size', function ($width, $height) {
    (new Pdf($this->testFile))->size($width, $height);
})
    ->throws(\Spatie\PdfToImage\Exceptions\InvalidSize::class)
    ->with([
        'invalid width' => [-1, 100],
        'invalid height' => [100, -1],
        'invalid size' => [-1, -1],
    ]);
