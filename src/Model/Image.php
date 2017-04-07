<?php

namespace eLife\ApiSdk\Model;

final class Image
{
    private $altText;
    private $uri;
    private $source;
    private $width;
    private $height;
    private $focalPointX;
    private $focalPointY;

    /**
     * @internal
     */
    public function __construct(string $altText, string $uri, File $source, int $width, int $height, int $focalPointX, int $focalPointY)
    {
        $this->altText = $altText;
        $this->uri = $uri;
        $this->source = $source;
        $this->width = $width;
        $this->height = $height;
        $this->focalPointX = $focalPointX;
        $this->focalPointY = $focalPointY;
    }

    public function getAltText() : string
    {
        return $this->altText;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getSource() : File
    {
        return $this->source;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function getFocalPointX() : int
    {
        return $this->focalPointX;
    }

    public function getFocalPointY() : int
    {
        return $this->focalPointY;
    }
}
