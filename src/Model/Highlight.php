<?php

namespace eLife\ApiSdk\Model;

final class Highlight implements Model, HasThumbnail
{
    private $title;
    private $image;
    private $item;
    private $impactStatement;

    /**
     * @internal
     */
    public function __construct(string $title, Image $image = null, Model $item, string $impactStatement = null)
    {
        $this->title = $title;
        $this->image = $image;
        $this->item = $item;
        $this->impactStatement = $impactStatement;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->image;
    }

    public function getItem() : Model
    {
        return $this->item;
    }

    public function getImpactStatement(): ?string
    {
        return $this->impactStatement;
    }
}
