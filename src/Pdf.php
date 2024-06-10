<?php

namespace Spatie\PdfToImage;

use Imagick;
use Spatie\PdfToImage\DTOs\PageSize;
use Spatie\PdfToImage\DTOs\PdfPage;
use Spatie\PdfToImage\Enums\LayerMethod;
use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Exceptions\InvalidLayerMethod;
use Spatie\PdfToImage\Exceptions\InvalidQuality;
use Spatie\PdfToImage\Exceptions\InvalidSize;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

class Pdf
{
    protected string $filename;

    protected int $resolution = 144;

    protected OutputFormat $outputFormat = OutputFormat::Jpg;

    protected array $pages = [1];

    public $imagick;

    protected LayerMethod $layerMethod = LayerMethod::Flatten;

    protected $colorspace;

    protected ?int $compressionQuality = null;

    protected ?int $thumbnailWidth = null;

    protected ?int $thumbnailHeight = null;

    protected ?int $resizeWidth = null;

    protected ?int $resizeHeight = null;

    protected ?int $numberOfPages = null;

    public function __construct(string $filename)
    {
        if (! file_exists($filename)) {
            throw PdfDoesNotExist::for($filename);
        }

        $this->filename = $filename;
    }

    /**
     * Sets the resolution of the generated image in DPI.
     * Default is 144 DPI.
     */
    public function resolution(int $dpiResolution): static
    {
        $this->resolution = $dpiResolution;

        return $this;
    }

    /**
     * Sets the output format of the generated image.
     * Default is OutputFormat::Jpg.
     */
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

    /**
     * Returns the number of pages in the PDF.
     */
    public function pageCount(): int
    {
        if ($this->imagick === null) {
            $this->imagick = new Imagick();
            $this->imagick->pingImage($this->filename);
        }

        if ($this->numberOfPages === null) {
            $this->numberOfPages = $this->imagick->getNumberImages();
        }

        return $this->numberOfPages;
    }

    /**
     * Returns a DTO representing the size of the PDF, which
     * contains the width and height in pixels.
     */
    public function getSize(): PageSize
    {
        if ($this->imagick === null) {
            $this->imagick = new Imagick();
            $this->imagick->pingImage($this->filename);
        }

        $geometry = $this->imagick->getImageGeometry();

        return PageSize::make($geometry['width'], $geometry['height']);
    }

    /**
     * Saves the PDF as an image. Expects a path to save the image to, which should be
     * a directory if multiple pages have been selected (otherwise the image will be overwritten).
     * Returns an array of paths to the saved images.
     *
     * @return array<string>
     */
    public function save(string $pathToImage, string $prefix = ''): array
    {
        $pages = [PdfPage::make($this->pages[0], $this->outputFormat, $prefix, $pathToImage)];

        if (is_dir($pathToImage)) {
            $pages = array_map(fn ($page) => PdfPage::make($page, $this->outputFormat, $prefix, rtrim($pathToImage, '\/').DIRECTORY_SEPARATOR.$page.'.'.$this->outputFormat->value), $this->pages);
        }

        $result = [];

        foreach ($pages as $page) {
            $path = $page->filename();
            $imageData = $this->getImageData($path, $page->number);

            if (file_put_contents($path, $imageData) !== false) {
                $result[] = $path;
            }
        }

        return $result;
    }

    /**
     * Saves all pages of the PDF as images. Expects a directory to save the images to,
     * and an optional prefix for the image filenames. Returns an array of paths to the saved images.
     *
     * @return array<string>
     */
    public function saveAllPages(string $directory, string $prefix = ''): array
    {
        $numberOfPages = $this->pageCount();

        if ($numberOfPages === 0) {
            return [];
        }

        $this->selectPages(...range(1, $numberOfPages));

        return $this->save($directory, $prefix);
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

        $this->imagick->readImage(sprintf('%s[%s]', $this->filename, $pageNumber - 1));

        if ($this->resizeWidth !== null) {
            $this->imagick->resizeImage($this->resizeWidth, $this->resizeHeight ?? 0, Imagick::FILTER_POINT, 0);
        }

        if ($this->layerMethod !== LayerMethod::None) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod->value);
        }

        if ($this->thumbnailWidth !== null) {
            $this->imagick->thumbnailImage($this->thumbnailWidth, $this->thumbnailHeight ?? 0);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage)->value);

        return $this->imagick;
    }

    public function colorspace(int $colorspace): static
    {
        $this->colorspace = $colorspace;

        return $this;
    }

    /**
     * Set the compression quality for the image. The value should be between 1 and 100, where
     * 1 is the lowest quality and 100 is the highest.
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidQuality
     */
    public function quality(int $compressionQuality): static
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
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidSize
     */
    public function thumbnailSize(int $width, ?int $height = null): static
    {
        if ($width < 0) {
            throw InvalidSize::forThumbnail($width, 'width');
        }

        if ($height !== null && $height < 0) {
            throw InvalidSize::forThumbnail($height, 'height');
        }

        $this->thumbnailWidth = $width;
        $this->thumbnailHeight = $height ?? 0;

        return $this;
    }

    /**
     * Set the size of the image. If no height is provided, the height will be scaled according to the width.
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidSize
     */
    public function size(int $width, ?int $height = null): static
    {
        if ($width < 0) {
            throw InvalidSize::forImage($width, 'width');
        }

        if ($height !== null && $height < 0) {
            throw InvalidSize::forImage($height, 'height');
        }

        $this->resizeWidth = $width;
        $this->resizeHeight = $height ?? 0;

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

    /**
     * Validate that the page numbers are within the range of the PDF, which is 1 to the number of pages.
     * Throws a PageDoesNotExist exception if a page number is out of range.
     *
     * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    protected function validatePageNumbers(int ...$pageNumbers): void
    {
        $count = $this->pageCount();

        foreach ($pageNumbers as $page) {
            if ($page > $count || $page < 1) {
                throw PageDoesNotExist::for($page);
            }
        }
    }
}
