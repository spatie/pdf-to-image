<?php

use Spatie\PdfToImage\Pdf;

afterAll(function() {
    if (file_exists(test_file('wide-2-1.jpg'))) {
        unlink(test_file('wide-2-1.jpg'));
    }

    if (file_exists(test_file('wide-1.jpg'))) {
        unlink(test_file('wide-1.jpg'));
    }
});

it('ensures that ultra-wide PDFs can be used', function () {
    $pdf = new Pdf(test_file('wide-2', 'pdf'));

    $size = $pdf->getSize();

    expect($size->width)->toBeGreaterThanOrEqual(94968);
    expect($size->height)->toBeGreaterThanOrEqual(42516);
});

it('ensures that wide PDFs can be used', function () {
    $filename = __DIR__.'/../files/test-wide-1.pdf';
    $pdf = new Pdf($filename);
    $size = $pdf->getSize();
    $filenames = $pdf->save(test_file('wide-1', 'jpg'));

    expect($size->width)->toBeGreaterThanOrEqual(1700);
    expect($size->height)->toBeGreaterThanOrEqual(800);
    expect(basename($filenames[0]))->toBe('test-wide-1.jpg');
});
