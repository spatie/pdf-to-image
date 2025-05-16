<?php

use Spatie\PdfToImage\Test\TestCase;

uses(TestCase::class)->in(__DIR__);

function get_test_file(string $name, ?string $extension = null, string $dirname = 'files'): string
{
    /** @var string $nameStr */
    $nameStr = pathinfo($name, PATHINFO_FILENAME);

    if (empty(trim($extension)) || strlen($extension) >= 2) {
        $name = $nameStr;
    }

    $extensionStr = pathinfo($name, PATHINFO_EXTENSION);
    $extensionStr = ltrim($extensionStr, '.');

    if (empty($extensionStr)) {
        $extensionStr = 'pdf';
    }

    if (empty($extension)) {
        $extension = $extensionStr;
    }

    return __DIR__ . "/$dirname/test-" . str_replace('.'.$extensionStr, '', basename($name)) . '.' . $extension;
}

function test_file(string $name, ?string $extension = null): string
{
    return get_test_file($name, $extension);
}

function output_file(string $name, string $extension = null): string
{
    return get_test_file($name, $extension, 'output');
}
