<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image as ImageModel;

final class Video implements Block, Asset
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $sources;
    private $placeholder;
    private $width;
    private $height;
    private $autoplay;
    private $loop;
    private $sourceData;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $id = null,
        string $label = null,
        string $title = null,
        Sequence $caption,
        array $sources,
        ImageModel $placeholder = null,
        int $width,
        int $height,
        bool $autoplay = false,
        bool $loop = false,
        array $sourceData = []
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->sources = $sources;
        $this->placeholder = $placeholder;
        $this->width = $width;
        $this->height = $height;
        $this->autoplay = $autoplay;
        $this->loop = $loop;
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
     * @return Sequence|Block[]
     */
    public function getCaption() : Sequence
    {
        return $this->caption;
    }

    /**
     * @return VideoSource[]
     */
    public function getSources() : array
    {
        return $this->sources;
    }

    /**
     * @return ImageModel|null
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function isAutoplay() : bool
    {
        return $this->autoplay;
    }

    public function isLoop() : bool
    {
        return $this->loop;
    }

    /**
     * @return AssetFile[]
     */
    public function getSourceData() : array
    {
        return $this->sourceData;
    }
}
