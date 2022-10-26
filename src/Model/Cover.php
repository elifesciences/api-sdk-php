<?php

namespace eLife\ApiSdk\Model;

final class Cover implements Model, HasBanner
{
    private $title;
    private $impactStatement;
    private $image;
    private $item;

    /**
     * @internal
     */
    public function __construct(string $title, Image $image, Model $item, $impactStatement = null)
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

    public function getBanner() : Image
    {
        return $this->image;
    }

    public function getItem() : Model
    {
        return $this->item;
    }

    public function getImpactStatement()
    {
        return $this->impactStatement;
    }
}
