<?php

namespace eLife\ApiSdk\Model;

final class Cover implements Model
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

    public function getImage() : Image
    {
        return $this->image;
    }

    public function getItem() : Model
    {
        return $this->item;
    }
}
