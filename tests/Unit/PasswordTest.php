<?php

use Spatie\PdfToImage\Exceptions\PasswordContainsUnsupportedCharacters;
use Spatie\PdfToImage\Exceptions\PasswordNotSupported;
use Spatie\PdfToImage\Pdf;

it('can convert a password-protected pdf when the correct password is given', function () {
    $imagick = (new Pdf($this->passwordProtectedTestFile))
        ->password('secret')
        ->getImageData('page-1.jpg', 1);

    expect($imagick)->toBeInstanceOf(Imagick::class);
})->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('can save a password-protected pdf as an image', function () {
    $path = $this->outputDirectory.'/page-1.jpg';

    (new Pdf($this->passwordProtectedTestFile))
        ->password('secret')
        ->save($path);

    expect($path)->toBeFile();
})->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('can count the pages of a password-protected pdf', function () {
    $pageCount = (new Pdf($this->passwordProtectedTestFile))
        ->password('secret')
        ->pageCount();

    expect($pageCount)->toEqual(1);
})->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('opens a password-protected pdf only when the password is correct', function () {
    expect((new Pdf($this->passwordProtectedTestFile))->password('secret')->pageCount())->toEqual(1);

    expect(fn () => (new Pdf($this->passwordProtectedTestFile))->password('wrong-password')->getImageData('page-1.jpg', 1))
        ->toThrow(ImagickException::class);

    expect(fn () => (new Pdf($this->passwordProtectedTestFile))->getImageData('page-1.jpg', 1))
        ->toThrow(ImagickException::class);
})->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('throws when setting a password on an ImageMagick version that ignores it', function () {
    expect(fn () => (new Pdf($this->passwordProtectedTestFile))->password('secret'))
        ->toThrow(PasswordNotSupported::class);
})->skip(fn () => Pdf::supportsPasswordProtectedPdfs(), 'Only applies to ImageMagick 6');

it('can read metadata after a first attempt failed without a password', function () {
    $pdf = new Pdf($this->passwordProtectedTestFile);

    expect(fn () => $pdf->pageCount())->toThrow(ImagickException::class);

    expect($pdf->password('secret')->pageCount())->toEqual(1);
    expect($pdf->getSize()->width)->toBeGreaterThan(0);
})->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('can convert a pdf protected by a password full of special characters', function () {
    $pageCount = (new Pdf($this->passwordWithSpecialCharactersTestFile))
        ->password($this->passwordWithSpecialCharacters)
        ->pageCount();

    expect($pageCount)->toEqual(1);
})->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('throws when the password holds a character that ImageMagick mangles', function (string $password) {
    expect(fn () => (new Pdf($this->passwordProtectedTestFile))->password($password))
        ->toThrow(PasswordContainsUnsupportedCharacters::class);
})->with([
    "secret's",
    'gehéim',
    '密码',
    "secret\n",
])->skip(fn () => ! Pdf::supportsPasswordProtectedPdfs(), 'Requires ImageMagick 7');

it('reports the characters ImageMagick hands over to Ghostscript unchanged', function () {
    expect(Pdf::supportedPasswordCharacters())
        ->toContain('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789 $-_.+!;*(),{}|^~[]`><#%/?:@&=')
        ->and(strspn($this->passwordWithSpecialCharacters, Pdf::supportedPasswordCharacters()))
        ->toEqual(strlen($this->passwordWithSpecialCharacters));
});
