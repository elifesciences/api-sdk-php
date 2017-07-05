<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\HasAttribution;
use eLife\ApiSdk\Model\Image as ImageModel;

final class Image implements BlockWithCaption, HasAttribution
{
    private $id;
    private $title;
    private $caption;
    private $image;

    /**
     * @internal
     */
    public function __construct(
        string $id = null,
        string $title = null,
        Sequence $caption,
        ImageModel $image
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->caption = $caption;
        $this->image = $image;
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
}
