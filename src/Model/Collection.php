<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Model\Image;

final class Collection
{
    private $id;
    private $title;
    private $impactStatement;
    private $publishedDate;

    public function __construct($id, $title, $impactStatement, DateTimeImmutable $publishedDate, Image $thumbnail)
    {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->thumbnail = $thumbnail;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getPublishedDate()
    {
        return $this->publishedDate; 
    }

    public function getThumbnail() : Image
    {
        return $this->thumbnail; 
    }
}
