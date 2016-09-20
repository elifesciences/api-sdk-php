<?php

namespace eLife\ApiSdk\Model;

use OutOfBoundsException;

final class Image
{
    private $altText;
    private $sizes;

    /**
     * @internal
     *
     * @param ImageSize[] $sizes
     */
    public function __construct(string $altText, array $sizes)
    {
        $this->altText = $altText;
        $this->sizes = $sizes;
    }

    public function getAltText() : string
    {
        return $this->altText;
    }

    /**
     * @return ImageSize[]
     */
    public function getSizes() : array
    {
        return $this->sizes;
    }

    public function getSize(string $ratio) : ImageSize
    {
        foreach ($this->sizes as $size) {
            if ($ratio === $size->getRatio()) {
                return $size;
            }
        }

        throw new OutOfBoundsException('No images with the size '.$ratio.' available');
    }
}
