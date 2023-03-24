<?php

use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

beforeEach(function () {
    $this->testFile = __DIR__.'/files/test.pdf';
    $this->multipageTestFile = __DIR__.'/files/multipage-test.pdf';
    $this->remoteFileUrl = 'https://tcd.blackboard.com/webapps/dur-browserCheck-BBLEARN/samples/sample.pdf';
});


it('will throw an exception when try to convert a non existing file', function () {
    new Pdf('pdfdoesnotexists.pdf');
})->throws(PdfDoesNotExist::class, 'File `pdfdoesnotexists.pdf` does not exist');

it('will throw an exception when try to convert a directory', function () {
    new Pdf('.');
})->throws(PdfDoesNotExist::class, 'Path `.` exists but is not a file');

it('will throw an exception when trying to convert an invalid file type', function () {
    (new Pdf($this->testFile))->setOutputFormat('bla');
})->throws(InvalidFormat::class);

it('will throw an exception when passed an invalid page number', function ($invalidPage) {
    (new Pdf($this->testFile))->setPage(100);
})
->throws(PageDoesNotExist::class)
->with([5, 0, -1]);

it('will correctly return the number of pages in pdf file', function () {
    $pdf = new Pdf($this->multipageTestFile);

    expect($pdf->getNumberOfPages())->toEqual(3);
});

it('will accept a custom specified resolution', function () {
    $image = (new Pdf($this->testFile))
        ->setResolution(150)
        ->getImageData('test.jpg')
        ->getImageResolution();

    expect($image['x'])->toEqual(150);
    expect($image['y'])->toEqual(150);
});

it('will convert a specified page', function () {
    $imagick = (new Pdf($this->multipageTestFile))
        ->setPage(2)
        ->getImageData('page-2.jpg');

    expect($imagick)->toBeInstanceOf(Imagick::class);
});

it('will accpect a specified file type and convert to it', function () {
    $imagick = (new Pdf($this->testFile))
        ->setOutputFormat('png')
        ->getImageData('test.png');

    expect($imagick->getFormat())->toEqual('png');
    expect($imagick->getFormat())->not->toEqual('jpg');
});

it('can accepct a layer', function () {
    $image = (new Pdf($this->testFile))
        ->setLayerMethod(Imagick::LAYERMETHOD_FLATTEN)
        ->setResolution(72)
        ->getImageData('test.jpg')
        ->getImageResolution();

    expect($image['x'])->toEqual(72);
    expect($image['y'])->toEqual(72);
});

it('will set compression quality', function () {
    $imagick = (new Pdf($this->testFile))
        ->setCompressionQuality(99)
        ->getImageData('test.jpg');

    expect($imagick->getCompressionQuality())->toEqual(99);
});

it('will create a thumbnail at specified width', function () {
    $imagick = (new Pdf($this->multipageTestFile))
       ->width(400)
       ->getImageData('test.jpg')
       ->getImageGeometry();

    expect($imagick['width'])->toBe(400);
});
