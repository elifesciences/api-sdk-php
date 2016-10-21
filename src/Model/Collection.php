<?php

namespace eLife\ApiSdk\Model;

final class Collection
{
    private $id;
    private $title;

    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
