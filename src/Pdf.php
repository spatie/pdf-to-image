<?php

namespace Spatie\PdfToImage;

use Imagick;
use Spatie\PdfToImage\Exceptions\InvalidColorSpace;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

class Pdf
{
    protected $pdfFile;

    protected $resolution = 144;

    protected $outputFormat = '';

    protected $outputColorSpace = Imagick::COLORSPACE_RGB;

    protected $page = 1;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];

    /**
     * @param string $pdfFile The path to the pdffile.
     *
     * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist
     */
    public function __construct($pdfFile)
    {
        if (!file_exists($pdfFile)) {
            throw new PdfDoesNotExist();
        }

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
        if (!$this->isValidOutputFormat($outputFormat)) {
            throw new InvalidFormat('Format '.$outputFormat.' is not supported');
        }

        $this->outputFormat = $outputFormat;

        return $this;
    }

    /**
     * Set the output color space.
     *
     * @param string $outputColorSpace
     *
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidColorspace
     */
    public function setOutputColorSpace($outputColorSpace)
    {        
        if (!$this->isValidOutputColorSpace($outputColorSpace)) {
            throw new InvalidColorSpace('Colorspace '.$outputColorSpace.' is not valid');
        }

        $this->outputColorSpace = constant('Imagick::'.$outputColorSpace);

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
     * Determine if the given colorspace is a valid.
     *
     * @param $outputFormat
     *
     * @return bool
     */
    public function isValidOutputColorSpace($outputColorSpace)
    {                
        return defined('Imagick::'.$outputColorSpace);        
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
        if ($page > $this->getNumberOfPages()) {
            throw new PageDoesNotExist('Page '.$page.' does not exist');
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
        return (new \Imagick($this->pdfFile))->getNumberImages();
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
        $imageData = $this->getImageData($pathToImage);

        file_put_contents($pathToImage, $imageData);

        return true;
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
        $imagick = new \Imagick();

        $imagick->setResolution($this->resolution, $this->resolution);

        $imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $this->page - 1));
        
        $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

        $imagick->setImageFormat($this->determineOutputFormat($pathToImage));

        $imagick->transformImageColorspace($this->outputColorSpace);

        $imagick->setFormat($this->determineOutputFormat($pathToImage));

        file_put_contents($pathToImage, $imagick);

        return $imagick;
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

        if (!$this->isValidOutputFormat($outputFormat)) {
            $outputFormat = 'jpg';
        }

        return $outputFormat;
    }
}
