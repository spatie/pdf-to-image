<?php

namespace Spatie\PdfToImage\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public $testFile = __DIR__.'/files/test.pdf';

    public $multipageTestFile = __DIR__.'/files/multipage-test.pdf';

    public $outputDirectory = __DIR__.'/output';

    public function unlinkAllOutputImages(string $path): void
    {
        $files = glob($path.'/*');

        foreach ($files as $file) {
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'gitignore') {
                unlink($file);
            }
        }
    }

    public function afterEach()
    {
        $this->unlinkAllOutputImages($this->outputDirectory);
    }
}
