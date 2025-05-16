<?php

use Spatie\PdfToImage\Test\TestCase;

uses(TestCase::class)->in(__DIR__);

function test_file(string $name, string $extension = null): string
{
    if (empty($extension)) {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $extension = ltrim($extension, '.');

        $name = pathinfo($name, PATHINFO_FILENAME);
    }

    $result = __DIR__ . '/files/test-' . $name . '.' . $extension;
    return $result;
}
