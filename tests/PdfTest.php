<?php

namespace Spatie\PdfToImage\Test;

use Spatie\PdfToImage\Exceptions\InvalidColorSpace;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;
use Imagick;

class PdfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $testFile;

    public function setUp()
    {
        parent::setUp();

        $this->testFile = __DIR__.'/files/test.pdf';
        $this->testImage = __DIR__.'/files/test.jpg';
    }

    /** @test */
    public function it_will_throw_an_exception_when_try_to_convert_an_non_existing_file()
    {
        $this->setExpectedException(PdfDoesNotExist::class);

        new Pdf('pdfdoesnotexists.pdf');
    }

    /** @test */
    public function it_will_throw_an_exception_when_try_to_convert_to_an_invalid_file_type()
    {
        $this->setExpectedException(InvalidFormat::class);

        (new Pdf($this->testFile))->setOutputFormat('bla');
    }

    /** @test */
    public function setting_colorspace_is_reflected_in_output_image()
    {
        $pdf = new Pdf($this->testFile);
        $pdf->setPage(1)->setResolution('72')->setOutputColorSpace('COLORSPACE_SRGB')->saveImage($this->testImage);
        $imagick = new Imagick();
        $imagick->readImage($this->testImage);

        $this->assertEquals(Imagick::COLORSPACE_SRGB, $imagick->getImageColorspace());
    }

    /** @test */
    public function it_will_throw_an_exception_when_try_to_use_an_invalid_colorspace()
    {
        $this->setExpectedException(InvalidColorSpace::class);

        (new Pdf($this->testImage))->setOutputColorSpace('FOOBAR');
    }
}
