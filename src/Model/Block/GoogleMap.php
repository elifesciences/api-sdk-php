<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\HasId;

final class GoogleMap implements BlockWithCaption, HasId
{
    private $id;
    private $title;
    private $caption;
    private $width;
    private $height;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title = null,
        Sequence $caption,
        int $width,
        int $height
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->caption = $caption;
        $this->width = $width;
        $this->height = $height;
    }

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getCaption() : Sequence
    {
        return $this->caption;
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
