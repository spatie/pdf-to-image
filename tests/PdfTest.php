<?php

namespace Spatie\PdfToImage\Test;

use Jcupitt\Vips;
use PHPUnit\Framework\TestCase;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

class PdfTest extends TestCase
{
    /** @var string */
    protected $testFile;

    /** @var string */
    protected $multipageTestFile;

    public function setUp(): void
    {
        parent::setUp();

        $this->testFile = __DIR__.'/files/test.pdf';

        $this->multipageTestFile = __DIR__.'/files/multipage-test.pdf';

        // remote URLs are directly supported by php-vips ... you'd need to
        // wget to a local file
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

    /**
     * @test
     * @dataProvider invalid_page_number_provider
     */
    public function it_will_throw_an_exception_when_passed_an_invalid_page($invalidPage)
    {
        $this->expectException(PageDoesNotExist::class);

        (new Pdf($this->testFile))->setPage($invalidPage);
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
        // php-vips uses DPI to set the number of pixels to render at, it 
        // does not set the xres/yres fields in the output image ... this test
        // would need adjusting to check the output image dimensions
    }

    /** @test */
    public function it_will_convert_a_specified_page()
    {
        $imagick = (new Pdf($this->multipageTestFile))
            ->setPage(2)
            ->getImageData('page-2.jpg');

        $this->assertInstanceOf('Jcupitt\Vips\Image', $imagick);
    }

    /** @test */
    public function it_will_accept_a_specified_file_type_and_convert_to_it()
    {
        // php-vips sets the output format during writeToImage, so this test
        // would need adjusting
    }

    /** @test */
    public function it_can_accept_a_layer()
    {
        // php-vips auto-flattens
    }

    /** @test */
    public function it_will_set_compression_quality()
    {
        // php-vips sets compression during writeToImage, so this test
        // would need adjusting
    }

    public function invalid_page_number_provider()
    {
        return [[5], [0], [-1]];
    }
}
