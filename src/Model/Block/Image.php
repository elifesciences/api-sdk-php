<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Image implements Block
{
    private $images;

    /**
     * @internal
     */
    public function __construct(ImageFile ...$images)
    {
        $this->images = $images;
    }

    public function getImage() : ImageFile
    {
        return $this->images[0];
    }

    /**
     * @return ImageFile[]
     */
    public function getSupplements() : array
    {
        return array_slice($this->images, 1);
    }
}
