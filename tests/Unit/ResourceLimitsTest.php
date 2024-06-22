<?php

use Spatie\PdfToImage\Enums\ResourceLimitType;
use Spatie\PdfToImage\Pdf;

it('sets the area resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Area, 1024 * 1024 * 128)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Area->value))->toBe((int) 1024 * 1024 * 128);
});

it('sets the disk resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Disk, 1024 * 1024 * 128)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Disk->value))->toBe((int) 1024 * 1024 * 128);
});

it('sets the map resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Map, 1024 * 1024 * 128)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Map->value))->toBe((int) 1024 * 1024 * 128);
});

it('sets the memory resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Memory, 1024 * 1024 * 128)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Memory->value))->toBe((int) 1024 * 1024 * 128);
});

it('sets the time resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Time, 30)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Time->value))->toBe((int) 30);
});
