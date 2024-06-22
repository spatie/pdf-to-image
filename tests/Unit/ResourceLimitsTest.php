<?php

use Spatie\PdfToImage\Enums\ResourceLimitType;
use Spatie\PdfToImage\Pdf;

it('sets the area resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Area, 1024 * 1024 * 16)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Area->value))->toBe((int) 1024 * 1024 * 16);
});

it('sets the disk resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Disk, 1024 * 1024)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Disk->value))->toBe((int) 1024 * 1024);
});

it('sets the file resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::File, 5)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::File->value))->toBe((int) 5);
});

it('sets the map resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Map, 1024 * 1024 * 16)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Map->value))->toBe((int) 1024 * 1024 * 16);
});

it('sets the memory resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Memory, 1024 * 1024 * 32)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Memory->value))->toBe((int) 1024 * 1024 * 32);
});

it('sets the time resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Time, 10)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Time->value))->toBe((int) 10);
});

it('sets the throttle resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Throttle, 10)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Throttle->value))->toBe((int) 10);
});

it('sets the thread resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Thread, 1)
        ->getImageData($this->testFile, 1);

    expect((int)$im::getResourceLimit(ResourceLimitType::Thread->value))->toBe((int)1);
});
