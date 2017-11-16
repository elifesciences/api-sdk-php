<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\Image as ImageModel;

final class Profile implements Block, HasContent
{
    private $image;
    private $content;

    /**
     * @internal
     */
    public function __construct(ImageModel $image, Sequence $content)
    {
        $this->image = $image;
        $this->content = $content;
    }

    public function getImage() : ImageModel
    {
        return $this->image;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }
}
