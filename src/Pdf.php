<?php

namespace Spatie\PdfToImage;

use Jcupitt\Vips;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

class Pdf
{
    protected $pdfFile;

    protected $resolution = 144;

    protected $outputFormat = 'jpg';

    protected $page = 1;

    protected $numberOfPages;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];

    protected $compressionQuality;

    public function __construct(string $pdfFile)
    {
        $this->pdfFile = $pdfFile;

        if (! file_exists($this->pdfFile)) {
            throw new PdfDoesNotExist("File `{$this->pdfFile}` does not exist");
        }

        // php-vips will just read the header and not decode the whole file, so
        // this is quick
        $image = Vips\Image::newFromFile($this->pdfFile);
        $this->numberOfPages = $image->get("n-pages");
    }

    public function setResolution(int $resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function setOutputFormat(string $outputFormat)
    {
        if (! $this->isValidOutputFormat($outputFormat)) {
            throw new InvalidFormat("Format {$outputFormat} is not supported");
        }

        $this->outputFormat = $outputFormat;

        return $this;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    /**
     * Sets the layer method for Imagick::mergeImageLayers()
     * If int, should correspond to a predefined LAYERMETHOD constant.
     * If null, Imagick::mergeImageLayers() will not be called.
     *
     * @param int|null
     *
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidLayerMethod
     *
     * @see https://secure.php.net/manual/en/imagick.constants.php
     * @see Pdf::getImageData()
     */
    public function setLayerMethod(?int $layerMethod)
    {
        // not needed by php-vips, I think
        return null;
    }

    public function isValidOutputFormat(string $outputFormat): bool
    {
        return in_array($outputFormat, $this->validOutputFormats);
    }

    public function setPage(int $page)
    {
        if ($page > $this->getNumberOfPages() || $page < 1) {
            throw new PageDoesNotExist("Page {$page} does not exist");
        }

        $this->page = $page;

        return $this;
    }

    public function getNumberOfPages(): int
    {
        return $this->numberOfPages;
    }

    public function saveImage(string $pathToImage): bool
    {
        if (is_dir($pathToImage)) {
            $pathToImage = rtrim($pathToImage, '\/').DIRECTORY_SEPARATOR.$this->page.'.'.$this->outputFormat;
        }

        $page = $this->getImageData($pathToImage);

        $compression = $this->compressionQuality !== null ?
            $this->compressionQuality : 75;

        // php-vips will set the image format from the suffix
        $page->writeToFile($pathToImage, [
            "Q" => $compression
        ]);

        // php-vips will throw an exception in writeToFile if there's an error
        return TRUE;
    }

    public function saveAllPagesAsImages(string $directory, string $prefix = ''): array
    {
        $numberOfPages = $this->getNumberOfPages();

        if ($numberOfPages === 0) {
            return [];
        }

        return array_map(function ($pageNumber) use ($directory, $prefix) {
            $this->setPage($pageNumber);

            $destination = "{$directory}/{$prefix}{$pageNumber}.{$this->outputFormat}";

            $this->saveImage($destination);

            return $destination;
        }, range(1, $numberOfPages));
    }

    public function getImageData(string $pathToImage): Vips\Image
    {
        $page = Vips\Image::newFromFile($this->pdfFile, [
            "dpi" => $this->resolution,
            "page" => $this->page - 1,
            # this enables image streaming
            "access" => "sequential"
        ]);

        return $page;
    }

    public function setColorspace(int $colorspace)
    {
        // php-vips always renders PDFs as RGB ... you'll need to use imagick if
        // you want CMYK
        return null;
    }

    public function setCompressionQuality(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;

        return $this;
    }

    protected function determineOutputFormat(string $pathToImage): string
    {
        $outputFormat = pathinfo($pathToImage, PATHINFO_EXTENSION);

        if ($this->outputFormat != '') {
            $outputFormat = $this->outputFormat;
        }

        $outputFormat = strtolower($outputFormat);

        if (! $this->isValidOutputFormat($outputFormat)) {
            $outputFormat = 'jpg';
        }

        return $outputFormat;
    }
}
