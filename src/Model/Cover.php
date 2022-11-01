<?php

namespace eLife\ApiSdk\Model;

final class Cover implements Model, HasImpactStatement, HasBanner
{
    private $title;
    private $impactStatement;
    private $image;
    private $item;

    /**
     * @internal
     */
    public function __construct(string $title, string $impactStatement = null, Image $image, Model $item)
    {
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->image = $image;
        $this->item = $item;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getBanner() : Image
    {
        return $this->image;
    }

    public function getItem() : Model
    {
        return $this->item;
    }
}
