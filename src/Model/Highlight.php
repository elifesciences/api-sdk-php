<?php

namespace eLife\ApiSdk\Model;

final class Highlight implements Model, HasThumbnail
{
    private $title;
    private $authorLine;
    private $image;
    private $item;

    /**
     * @internal
     */
    public function __construct(string $title, string $authorLine = null, Image $image = null, Model $item)
    {
        $this->title = $title;
        $this->authorLine = $authorLine;
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
    final public function getAuthorLine()
    {
        return $this->authorLine;
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
}
