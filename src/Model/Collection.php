<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;

final class Collection
{
    private $id;
    private $title;
    private $impactStatement;
    private $publishedDate;

    public function __construct($id, $title, $impactStatement, DateTimeImmutable $publishedDate)
    {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
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
}
