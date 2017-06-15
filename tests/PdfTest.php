<?php

namespace Spatie\PdfToImage\Test;

use PHPUnit\Framework\TestCase;
use Spatie\PdfToImage\Pdf;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PdfTest extends TestCase
{
    /** @var string */
    protected $testFile;

    /** @var string */
    protected $multipageTestFile;

    /** @var \Spatie\TemporaryDirectory\TemporaryDirectory */
    protected $temporaryDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->testFile = __DIR__ . '/files/test.pdf';

        $this->multipageTestFile = __DIR__ . '/files/multipage-test.pdf';

        $this->temporaryDirectory = new TemporaryDirectory(__DIR__ . '/temp');

        $this->temporaryDirectory
            ->force()
            ->empty();
    }

    /** @test */
    public function it_can_convert_a_pdf()
    {
        $image = (new pdf($this->multipageTestFile))
            ->saveImage($this->temporaryDirectory->path('image.jpg'));
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


}
