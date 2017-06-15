<?php

namespace Spatie\PdfToImage\Test;

use Spatie\PdfToImage\Pdf;
use PHPUnit\Framework\TestCase;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;

class PdfTest extends TestCase
{
    /**
     * @var string
     */
    protected $testFile;

    public function setUp()
    {
        parent::setUp();

        $this->testFile = __DIR__.'/files/test.pdf';
        $this->multipageTestFile = __DIR__.'/files/multipage-test.pdf';
    }

    /** @test */
    public function it_will_throw_an_exception_when_try_to_convert_a_non_existing_file()
    {
        $this->expectException(PdfDoesNotExist::class);

        new Pdf('pdfdoesnotexists.pdf');
    }

    /** @test */
    public function it_will_throw_an_exception_when_try_to_convert_to_an_invalid_file_type()
    {
        $this->expectException(InvalidFormat::class);

        (new Pdf($this->testFile))->setOutputFormat('bla');
    }

    /** @test */
    public function it_will_throw_an_exception_when_passed_an_invalid_page()
    {
        $this->expectException(PageDoesNotExist::class);

        (new Pdf($this->testFile))->setPage(5);
    }

    /** @test */
    public function it_will_correctly_return_the_number_of_pages_in_pdf_file()
    {
        $pdf = new Pdf($this->multipageTestFile);

        $this->assertTrue($pdf->getNumberOfPages() === 3);
    }

    /** @test */
    public function it_will_accept_a_custom_specified_resolution()
    {
        $pdf = new Pdf($this->testFile);

        $pdf->setResolution(72);

        $image = $pdf->getImageData('test.jpg')->getImageResolution();

        $this->assertEquals($image['x'], 72);
        $this->assertEquals($image['y'], 72);
        $this->assertNotEquals($image['x'], 144);
        $this->assertNotEquals($image['y'], 144);
    }

    /** @test */
    public function it_will_convert_a_specified_page()
    {
        $pdf = new Pdf($this->multipageTestFile);

        $pdf->setPage(2);

        $imagick = $pdf->getImageData('page-2.jpg');

        $this->assertInstanceOf('Imagick', $imagick);
    }

    /** @test */
    public function it_will_accept_a_specified_file_type_and_convert_to_it()
    {
        $pdf = new pdf($this->testFile);

        $pdf->setOutputFormat('png');

        $imagick = $pdf->getImageData('test.png');

        $this->assertSame($imagick->getFormat(), 'png');
        $this->assertNotSame($imagick->getFormat(), 'jpg');
    }
}
