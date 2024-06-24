<?php

use Spatie\PdfToImage\Enums\ResourceLimitType;
use Spatie\PdfToImage\Pdf;

beforeEach(function () {
    $this->memory128MbInBytes = 128000000;
});

it('sets the area resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Area, $this->memory128MbInBytes)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Area->value))->toEqual($this->memory128MbInBytes);
});

it('sets the disk resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Disk, $this->memory128MbInBytes)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Disk->value))->toEqual($this->memory128MbInBytes);
});

it('sets the map resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Map, $this->memory128MbInBytes)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Map->value))->toEqual($this->memory128MbInBytes);
});

it('sets the memory resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Memory, $this->memory128MbInBytes)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Memory->value))->toEqual($this->memory128MbInBytes);
});

it('sets the time resource limit', function () {
    $pdf = new Pdf($this->testFile);
    $im = $pdf->resourceLimit(ResourceLimitType::Time, 30)
        ->getImageData($this->testFile, 1);

    expect((int) $im::getResourceLimit(ResourceLimitType::Time->value))->toBe((int) 30);
});
