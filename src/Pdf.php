<?php

namespace Spatie\PdfToImage;

use Imagick;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\InvalidLayerMethod;

class Pdf
{
    protected $pdfFile;

    protected $resolution = 144;

    protected $outputFormat = 'jpg';

    protected $page = 1;

    public $imagick;

    protected $numberOfPages;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];

    protected $layerMethod = Imagick::LAYERMETHOD_FLATTEN;

    protected $colorspace;

    protected $compressionQuality;

    /**
     * @param string $pdfFile The path or url to the pdffile.
     *
     * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist
     */
    public function __construct($pdfFile)
    {
        if (! filter_var($pdfFile, FILTER_VALIDATE_URL) && ! file_exists($pdfFile)) {
            throw new PdfDoesNotExist();
        }

        $this->imagick = new Imagick($pdfFile);

        $this->numberOfPages = $this->imagick->getNumberImages();

        $this->pdfFile = $pdfFile;
    }

    /**
     * Set the raster resolution.
     *
     * @param int $resolution
     *
     * @return $this
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Set the output format.
     *
     * @param string $outputFormat
     *
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidFormat
     */
    public function setOutputFormat($outputFormat)
    {
        if (! $this->isValidOutputFormat($outputFormat)) {
            throw new InvalidFormat("Format {$outputFormat} is not supported");
        }

        $this->outputFormat = $outputFormat;

        return $this;
    }

    /**
     * Get the output format.
     *
     * @return string
     */
    public function getOutputFormat()
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
    public function setLayerMethod($layerMethod)
    {
        if (
            is_int($layerMethod) === false &&
            is_null($layerMethod) === false
        ) {
            throw new InvalidLayerMethod('LayerMethod must be an integer or null');
        }

        $this->layerMethod = $layerMethod;

        return $this;
    }

    /**
     * Determine if the given format is a valid output format.
     *
     * @param $outputFormat
     *
     * @return bool
     */
    public function isValidOutputFormat($outputFormat)
    {
        return in_array($outputFormat, $this->validOutputFormats);
    }

    /**
     * Set the page number that should be rendered.
     *
     * @param int $page
     *
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    public function setPage($page)
    {
        if ($page > $this->getNumberOfPages() || $page < 1) {
            throw new PageDoesNotExist("Page {$page} does not exist");
        }

        $this->page = $page;

        return $this;
    }

    /**
     * Get the number of pages in the pdf file.
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * Save the image to the given path.
     *
     * @param string $pathToImage
     *
     * @return bool
     */
    public function saveImage($pathToImage)
    {
        if (is_dir($pathToImage)) {
            $pathToImage = rtrim($pathToImage, '\/').DIRECTORY_SEPARATOR.$this->page.'.'.$this->outputFormat;
        }

        $imageData = $this->getImageData($pathToImage);

        return file_put_contents($pathToImage, $imageData) !== false;
    }

    /**
     * Save the file as images to the given directory.
     *
     * @param string $directory
     * @param string $prefix
     *
     * @return array $files the paths to the created images
     */
    public function saveAllPagesAsImages($directory, $prefix = '')
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

    /**
     * Return raw image data.
     *
     * @param string $pathToImage
     *
     * @return \Imagick
     */
    public function getImageData($pathToImage)
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
            return $this->getRemoteImageData($pathToImage);
        }

        $this->imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $this->page - 1));

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage));

        return $this->imagick;
    }

    /**
     * @param int $colorspace
     *
     * @return $this
     */
    public function setColorspace(int $colorspace)
    {
        $this->colorspace = $colorspace;

        return $this;
    }

    /**
     * @param int $compressionQuality
     *
     * @return $this
     */
    public function setCompressionQuality(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;

        return $this;
    }

    /**
     * Return remote raw image data.
     *
     * @param string $pathToImage
     *
     * @return \Imagick
     */
    protected function getRemoteImageData($pathToImage)
    {
        $this->imagick->readImage($this->pdfFile);

        $this->imagick->setIteratorIndex($this->page - 1);

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage));

        return $this->imagick;
    }

    /**
     * Determine in which format the image must be rendered.
     *
     * @param $pathToImage
     *
     * @return string
     */
    protected function determineOutputFormat($pathToImage)
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
