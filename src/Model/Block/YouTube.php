<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasId;

final class YouTube implements Block, HasId
{
    private $id;
    private $width;
    private $height;

    /**
     * @internal
     */
    public function __construct(string $id, int $width, int $height)
    {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }
}
