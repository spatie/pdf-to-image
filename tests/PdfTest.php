<?php

namespace Spatie\PdfToImage\Test;

use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

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
}
