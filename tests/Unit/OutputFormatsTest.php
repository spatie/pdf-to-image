<?php

use Spatie\PdfToImage\Pdf;

it('will accept an output format and convert to it', function ($format) {
    $fmt = \Spatie\PdfToImage\Enums\OutputFormat::from($format);

    $imagick = (new Pdf($this->testFile))
        ->format($fmt)
        ->getImageData('test.'.$fmt->value, 1);

    expect($imagick->getFormat())->toEqual($fmt->value);
})
    ->with(['jpg', 'png', 'webp']);

it('gets the output format', function () {
    $pdf = new Pdf($this->testFile);
    expect($pdf->getFormat()->value)->toEqual('jpg');

    $pdf->format(\Spatie\PdfToImage\Enums\OutputFormat::Png);
    expect($pdf->getFormat()->value)->toEqual('png');
});

it('checks if the provided output format string is a supported format', function ($formats, $expected) {
    $pdf = (new Pdf($this->testFile));

    foreach ($formats as $format) {
        expect($pdf->isValidOutputFormat($format))->toBe($expected);
    }
})
    ->with([
        'supported formats' => [['jpg', 'png', 'webp'], true],
        'unsupported formats' => [['bmp', 'gif', ''], false],
    ]);
