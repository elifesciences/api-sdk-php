<?php

namespace eLife\ApiSdk\Model;

final class Cover implements Model, HasBanner
{
    private $title;
    private $image;
    private $item;

    /**
     * @internal
     */
    public function __construct(string $title, Image $image, Model $item)
    {
        $this->title = $title;
        $this->image = $image;
        $this->item = $item;
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
}
