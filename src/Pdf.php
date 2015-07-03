<?php

namespace Spatie\PdfToImage;

use Spatie\ConvertPdfToImage\Exceptions\InvalidFormat;
use Spatie\ConvertPdfToImage\Exceptions\PageDoesNotExist;
use Spatie\ConvertPdfToImage\Exceptions\PdfDoesNotExist;

class Pdf
{
    protected $pdfFile;

    protected $resolution = 144;

    protected $outputFormat = '';

    protected $page = 1;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];

    /**
     * @param string $pdfFile The path to the pdffile.
     *
     * @throws \Spatie\ConvertPdfToImage\Exceptions\PdfDoesNotExist
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
     * @throws \Spatie\ConvertPdfToImage\Exceptions\InvalidFormat
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
     * @throws \Spatie\ConvertPdfToImage\Exceptions\PageDoesNotExist
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
        $imagick = new \Imagick();

        $imagick->setResolution($this->resolution, $this->resolution);

        $imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $this->page - 1));

        $imagick->setImageFormat($this->determineOutputFormat($pathToImage));

        file_put_contents($pathToImage, $imagick);

        return true;
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
