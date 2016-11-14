<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;

final class Video implements Block
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $sources;
    private $image;
    private $width;
    private $height;
    private $sourceData;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $id = null,
        string $label = null,
        string $title = null,
        array $caption,
        array $sources,
        string $image = null,
        int $width,
        int $height,
        array $sourceData = []
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->sources = $sources;
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
        $this->sourceData = $sourceData;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Block[]
     */
    public function getCaption() : array
    {
        return $this->caption;
    }

    /**
     * @return VideoSource[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return File[]
     */
    public function getSourceData(): array
    {
        return $this->sourceData;
    }
}
