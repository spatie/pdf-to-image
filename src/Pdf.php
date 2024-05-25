<?php

namespace Spatie\PdfToImage;

use Imagick;
use Spatie\PdfToImage\DTOs\PdfPage;
use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

class Pdf
{
    protected $pdfFile;

    protected int $resolution = 144;

    protected OutputFormat $outputFormat = OutputFormat::Jpg;

    protected array $pages = [1];

    public $imagick;

    protected $layerMethod = Imagick::LAYERMETHOD_FLATTEN;

    protected $colorspace;

    protected $compressionQuality;

    protected $thumbnailWidth;

    private $numberOfPages = null;

    public function __construct(string $pdfFile)
    {
        if (! file_exists($pdfFile)) {
            throw PdfDoesNotExist::forFile($pdfFile);
        }

        $this->pdfFile = $pdfFile;

        $this->imagick = new Imagick();

        $this->imagick->readImage($this->pdfFile);
    }

    public function resolution(int $dpiResolution)
    {
        $this->resolution = $dpiResolution;

        return $this;
    }

    public function format(OutputFormat $outputFormat)
    {
        $this->outputFormat = $outputFormat;

        return $this;
    }

    public function getFormat(): OutputFormat
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
    public function mergeLayerMethod(?int $method)
    {
        $this->layerMethod = $method;

        return $this;
    }

    /**
     * Expects a string or OutputFormat enum. If a string, expects the file extension of the format,
     * without a leading period.
     * @param string|null|OutputFormat $outputFormat
     * @return bool
     */
    public function isValidOutputFormat(null|string|OutputFormat $outputFormat): bool
    {
        if ($outputFormat === null) {
            return false;
        }

        if ($outputFormat instanceof OutputFormat) {
            return true;
        }

        return OutputFormat::tryFrom(strtolower($outputFormat)) !== null;
    }

    public function selectPage(int $page)
    {
        return $this->selectPages($page);
    }

    public function selectPages(int ...$pages)
    {
        $this->validatePageNumbers(...$pages);

        $this->pages = $pages;

        return $this;
    }

    public function pageCount(): int
    {
        if ($this->numberOfPages === null) {
            $this->numberOfPages = $this->imagick->getNumberImages();
        }

        return $this->numberOfPages;
    }

    /**
     * Saves the PDF as an image. Expects a path to save the image to, which should be
     * a directory if multiple pages have been selected (otherwise the image will be overwritten).
     * Returns either a string with a single filename that was written, or an array of paths to the saved images.
     * @param string $pathToImage
     * @param string $prefix
     * @return array|string
     */
    public function saveImage(string $pathToImage, string $prefix = ''): array|string
    {
        $pages = [PdfPage::make($this->pages[0], $this->outputFormat, $prefix, $pathToImage)];

        if (is_dir($pathToImage)) {
            $pages = array_map(fn($page) =>
                PdfPage::make($page, $this->outputFormat, $prefix, rtrim($pathToImage, '\/').DIRECTORY_SEPARATOR.$page.'.'.$this->outputFormat->value), $this->pages);
        }

        $result = [];

        foreach($pages as $page) {
            $path = $page->getFilename();
            $imageData = $this->getImageData($path, $page->number);

            if (file_put_contents($path, $imageData) !== false) {
                $result[] = $path;
            }
        }

        if (count($result) === 1) {
            return $result[0];
        }

        return $result;
    }

    public function saveAllPagesAsImages(string $directory, string $prefix = ''): bool
    {
        $numberOfPages = $this->pageCount();

        if ($numberOfPages === 0) {
            return false;
        }

        $this->selectPages(...range(1, $numberOfPages));

        return $this->saveImage($directory, $prefix);
    }

    public function getImageData(string $pathToImage, int $pageNumber): Imagick
    {
        /*
         * Reinitialize imagick because the target resolution must be set
         * before reading the actual image.
         */
        $this->imagick = new Imagick();

        $this->imagick->setResolution($this->resolution, $this->resolution);

        if ($this->colorspace !== null) {
            $this->imagick->setColorspace($this->colorspace);
        }

        if ($this->compressionQuality !== null) {
            $this->imagick->setCompressionQuality($this->compressionQuality);
        }

        if (filter_var($this->pdfFile, FILTER_VALIDATE_URL)) {
            return $this->getRemoteImageData($pathToImage, $pageNumber);
        }

        $this->imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $pageNumber - 1));

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        if ($this->thumbnailWidth !== null) {
            $this->imagick->thumbnailImage($this->thumbnailWidth, 0);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage)->value);

        return $this->imagick;
    }

    public function colorspace(int $colorspace)
    {
        $this->colorspace = $colorspace;

        return $this;
    }

    public function quality(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;

        return $this;
    }

    public function width(int $thumbnailWidth)
    {
        $this->thumbnailWidth = $thumbnailWidth;

        return $this;
    }

    protected function getRemoteImageData(string $pathToImage, int $pageNumber): Imagick
    {
        $this->imagick->readImage($this->pdfFile);

        $this->imagick->setIteratorIndex($pageNumber - 1);

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage)->value);

        return $this->imagick;
    }

    protected function determineOutputFormat(string $pathToImage): OutputFormat
    {
        $outputFormat = OutputFormat::tryFrom(pathinfo($pathToImage, PATHINFO_EXTENSION));

        if (!empty($this->outputFormat)) {
            $outputFormat = $this->outputFormat;
        }

        if (! $this->isValidOutputFormat($outputFormat)) {
            $outputFormat = OutputFormat::Jpg;
        }

        return $outputFormat;
    }

    protected function validatePageNumbers(int ...$pageNumbers)
    {
        foreach($pageNumbers as $page) {
            if ($page > $this->pageCount() || $page < 1) {
                throw PageDoesNotExist::forPage($page);
            }
        }
    }
}
