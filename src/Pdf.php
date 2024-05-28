<?php

namespace Spatie\PdfToImage;

use Imagick;
use Spatie\PdfToImage\DTOs\PdfPage;
use Spatie\PdfToImage\Enums\LayerMethod;
use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Exceptions\InvalidLayerMethod;
use Spatie\PdfToImage\Exceptions\InvalidQuality;
use Spatie\PdfToImage\Exceptions\InvalidThumbnailSize;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

class Pdf
{
    protected string $pdfFile;

    protected int $resolution = 144;

    protected OutputFormat $outputFormat = OutputFormat::Jpg;

    protected array $pages = [1];

    public $imagick;

    protected LayerMethod $layerMethod = LayerMethod::Flatten;

    protected $colorspace;

    protected ?int $compressionQuality = null;

    protected ?int $thumbnailWidth = null;

    protected ?int $thumbnailHeight = null;

    private ?int $numberOfPages = null;

    public function __construct(string $pdfFile)
    {
        if (! file_exists($pdfFile)) {
            throw PdfDoesNotExist::for($pdfFile);
        }

        $this->pdfFile = $pdfFile;

        $this->imagick = new Imagick();

        $this->imagick->readImage($this->pdfFile);
    }

    public function resolution(int $dpiResolution): static
    {
        $this->resolution = $dpiResolution;

        return $this;
    }

    public function format(OutputFormat $outputFormat): static
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
     * If int, should correspond to a predefined Imagick LAYERMETHOD constant.
     * If LayerMethod, should be a valid LayerMethod enum.
     * To disable merging image layers, set to LayerMethod::None.
     *
     * @param \Spatie\PdfToImage\Enums\LayerMethod|int
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidLayerMethod
     *
     * @see https://secure.php.net/manual/en/imagick.constants.php
     * @see Pdf::getImageData()
     */
    public function layerMethod(LayerMethod|int $method): static
    {
        if (is_int($method) && ! LayerMethod::isValid($method)) {
            throw InvalidLayerMethod::for($method);
        }

        $this->layerMethod = $method;

        return $this;
    }

    /**
     * Expects a string or OutputFormat enum. If a string, expects the file extension of the format,
     * without a leading period.
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

    public function selectPage(int $page): static
    {
        return $this->selectPages($page);
    }

    public function selectPages(int ...$pages): static
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
     */
    public function saveImage(string $pathToImage, string $prefix = ''): array|string
    {
        $pages = [PdfPage::make($this->pages[0], $this->outputFormat, $prefix, $pathToImage)];

        if (is_dir($pathToImage)) {
            $pages = array_map(fn ($page) => PdfPage::make($page, $this->outputFormat, $prefix, rtrim($pathToImage, '\/').DIRECTORY_SEPARATOR.$page.'.'.$this->outputFormat->value), $this->pages);
        }

        $result = [];

        foreach ($pages as $page) {
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

    public function saveAllPagesAsImages(string $directory, string $prefix = ''): array|string
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

        $this->imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $pageNumber - 1));

        if ($this->layerMethod !== LayerMethod::None) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod->value);
        }

        if ($this->thumbnailWidth !== null) {
            $this->imagick->thumbnailImage($this->thumbnailWidth, $this->thumbnailHeight ?? 0);
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
        if ($compressionQuality < 1 || $compressionQuality > 100) {
            throw InvalidQuality::for($compressionQuality);
        }

        $this->compressionQuality = $compressionQuality;

        return $this;
    }

    /**
     * Set the thumbnail size for the image. If no height is provided, the thumbnail height will
     * be scaled according to the width.
     *
     * @param  int  $height
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidThumbnailSize
     */
    public function thumbnailSize(int $width, ?int $height = null)
    {
        if ($width < 0) {
            throw InvalidThumbnailSize::forWidth($width);
        }

        if ($height !== null && $height < 0) {
            throw InvalidThumbnailSize::forHeight($height);
        }

        $this->thumbnailWidth = $width;
        $this->thumbnailHeight = $height ?? 0;

        return $this;
    }

    protected function determineOutputFormat(string $pathToImage): OutputFormat
    {
        $outputFormat = OutputFormat::tryFrom(pathinfo($pathToImage, PATHINFO_EXTENSION));

        if (! empty($this->outputFormat)) {
            $outputFormat = $this->outputFormat;
        }

        if (! $this->isValidOutputFormat($outputFormat)) {
            $outputFormat = OutputFormat::Jpg;
        }

        return $outputFormat;
    }

    protected function validatePageNumbers(int ...$pageNumbers)
    {
        foreach ($pageNumbers as $page) {
            if ($page > $this->pageCount() || $page < 1) {
                throw PageDoesNotExist::for($page);
            }
        }
    }
}
