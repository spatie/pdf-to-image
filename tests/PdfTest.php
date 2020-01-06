<?php

namespace Spatie\PdfToImage\Test;

use Imagick;
use Spatie\PdfToImage\Pdf;
use PHPUnit\Framework\TestCase;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;

class PdfTest extends TestCase
{
    /** @var string */
    protected $testFile;

    /** @var string */
    protected $multipageTestFile;

    /** @var string */
    protected $remoteFileUrl;

    public function setUp()
    {
        parent::setUp();

        $this->testFile = __DIR__.'/files/test.pdf';

        $this->multipageTestFile = __DIR__.'/files/multipage-test.pdf';

        $this->remoteFileUrl = 'https://tcd.blackboard.com/webapps/dur-browserCheck-BBLEARN/samples/sample.pdf';
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
        $image = (new Pdf($this->testFile))
            ->setResolution(150)
            ->getImageData('test.jpg')
            ->getImageResolution();

        $this->assertEquals($image['x'], 150);
        $this->assertEquals($image['y'], 150);
    }

    /** @test */
    public function it_will_convert_a_specified_page()
    {
        $imagick = (new Pdf($this->multipageTestFile))
            ->setPage(2)
            ->getImageData('page-2.jpg');

        $this->assertInstanceOf('Imagick', $imagick);
    }

    /** @test */
    public function it_will_accept_a_specified_file_type_and_convert_to_it()
    {
        $imagick = (new pdf($this->testFile))
            ->setOutputFormat('png')
            ->getImageData('test.png');

        $this->assertSame($imagick->getFormat(), 'png');
        $this->assertNotSame($imagick->getFormat(), 'jpg');
    }

    /** @test */
    public function it_can_accept_a_layer()
    {
        $image = (new Pdf($this->testFile))
            ->setLayerMethod(Imagick::LAYERMETHOD_FLATTEN)
            ->setResolution(72)
            ->getImageData('test.jpg')
            ->getImageResolution();

        $this->assertEquals($image['x'], 72);
        $this->assertEquals($image['y'], 72);
    }

    /** @test */
    public function it_will_set_compression_quality()
    {
        $imagick = (new Pdf($this->testFile))
            ->setCompressionQuality(99)
            ->getImageData('test.jpg');

        $this->assertEquals(99, $imagick->getCompressionQuality());
    }

    public function invalid_page_number_provider()
    {
        return [[5], [0], [-1]];
    }
}
