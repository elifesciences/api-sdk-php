<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\HasAttribution;
use eLife\ApiSdk\Model\Image as ImageModel;

final class Video implements BlockWithCaption, HasAttribution
{
    private $id;
    private $title;
    private $caption;
    private $attribution;
    private $sources;
    private $placeholder;
    private $width;
    private $height;
    private $autoplay;
    private $loop;

    /**
     * @internal
     */
    public function __construct(
        string $id = null,
        string $title = null,
        Sequence $caption,
        Sequence $attribution,
        array $sources,
        ImageModel $placeholder = null,
        int $width,
        int $height,
        bool $autoplay = false,
        bool $loop = false
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->caption = $caption;
        $this->attribution = $attribution;
        $this->sources = $sources;
        $this->placeholder = $placeholder;
        $this->width = $width;
        $this->height = $height;
        $this->autoplay = $autoplay;
        $this->loop = $loop;
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
     * @return Sequence|string[]
     */
    public function getAttribution() : Sequence
    {
        return $this->attribution;
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
}
