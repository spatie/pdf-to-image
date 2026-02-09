<?php

use Spatie\PdfToImage\Test\TestCase;

uses(TestCase::class)->in(__DIR__);

function get_test_file(string $name, ?string $extension = null, string $dirname = 'files'): string
{
    $extension = empty($extension) ? pathinfo($name, PATHINFO_EXTENSION) : $extension;
    $name = pathinfo($name, PATHINFO_FILENAME);
    $extension = ltrim($extension, '.');

    if (empty($extension)) {
        $extension = 'pdf';
    }

    return __DIR__."/$dirname/test-$name.$extension";
}

function test_file(string $name, ?string $extension = null): string
{
    return get_test_file($name, $extension);
}

function output_file(string $name, ?string $extension = null): string
{
    return get_test_file($name, $extension, 'output');
}
