<?php

use Spatie\PdfToImage\Pdf;

it('can convert a password-protected pdf when the correct password is given', function () {
    $imagick = (new Pdf($this->passwordProtectedTestFile))
        ->password('secret')
        ->getImageData('page-1.jpg', 1);

    expect($imagick)->toBeInstanceOf(Imagick::class);
});

it('can save a password-protected pdf as an image', function () {
    $path = $this->outputDirectory.'/page-1.jpg';

    (new Pdf($this->passwordProtectedTestFile))
        ->password('secret')
        ->save($path);

    expect($path)->toBeFile();
});

it('can count the pages of a password-protected pdf', function () {
    $pageCount = (new Pdf($this->passwordProtectedTestFile))
        ->password('secret')
        ->pageCount();

    expect($pageCount)->toEqual(1);
});

it('throws an exception when converting a password-protected pdf without a password', function () {
    (new Pdf($this->passwordProtectedTestFile))->getImageData('page-1.jpg', 1);
})->throws(ImagickException::class);

it('throws an exception when converting a password-protected pdf with the wrong password', function () {
    (new Pdf($this->passwordProtectedTestFile))
        ->password('wrong-password')
        ->getImageData('page-1.jpg', 1);
})->throws(ImagickException::class);
