<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasId;

final class Figshare implements Block, HasId
{
    private $id;
    private $title;
    private $width;
    private $height;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        int $width,
        int $height
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
    }

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
