<?php

namespace Spatie\PdfToImage;

use Spatie\ConvertPdfToImage\PdfDoesNotExist;

class Pdf
{
    protected $pdfFile;

    protected $resolution = 144;

    protected $outputFormat = '';

    protected $page = 1;

    /**
     * @param string $pdfFile The path to the pdffile.
     *
     * @throws \Spatie\ConvertPdfToImage\PdfDoesNotExist
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
     */
    public function setOutputFormat($outputFormat)
    {
        $this->outputFormat = $outputFormat;
    }

    /**
     * Set the page number that should be rendered.
     *
     * @param int $page
     *
     * @throws \Spatie\PdfToImage\PageDoesNotExist
     */
    public function setPage($page)
    {
        if ($page > $this->getPageCount()) {
            throw new PageDoesNotExist('Page '.$page.' does not exist');
        }

        $this->page = $page;
    }

    /**
     * Get the number of pages in the pdf file.
     *
     * @return int
     */
    public function getPageCount()
    {
        return (new Imagick($this->pdfFile))->getNumberImages();
    }

    /**
     * Save the image to the given path.
     *
     * @param string $pathToImage
     *
     * @throws \Spatie\PdfToImage\InvalidFormat
     */
    public function saveImage($pathToImage)
    {
        $outputFormat = $this->determineOutputFormat($pathToImage);

        $im = new Imagick();
        $im->setResolution($this->resolution, $this->resolution);
        $im->readImage($this->pdfFile[$this->page - 1]);
        $im->setImageFormat($outputFormat);

        file_put_contents($pathToImage, $im);
    }

    protected function determineOutputFormat($pathToImage)
    {
        $outputFormat = pathinfo($pathToImage, PATHINFO_EXTENSION);

        if ($this->outputFormat != '') {
            $outputFormat = $this->outputFormat;
        }

        $outputFormat = strtolower($outputFormat);

        if (!in_array($outputFormat, ['jpg', 'jpeg', 'png'])) {
            $outputFormat = 'jpg';
        }

        return $outputFormat;
    }
}
