<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\HasAttribution;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\Image as ImageModel;

final class Image implements BlockWithCaption, HasAttribution, HasId
{
    private $id;
    private $title;
    private $caption;
    private $image;
    private $inline;

    /**
     * @internal
     */
    public function __construct(
        string $id = null,
        string $title = null,
        Sequence $caption,
        ImageModel $image,
        bool $inline = false
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->caption = $caption;
        $this->image = $image;
        $this->inline = $inline;
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
        return $this->image->getAttribution();
    }

    public function getImage() : ImageModel
    {
        return $this->image;
    }

    public function isInline() : bool
    {
        return $this->inline;
    }
}
