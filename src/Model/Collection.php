<?php

namespace eLife\ApiSdk\Model;

final class Collection
{
    private $id;
    private $title;
    private $impactStatement;

    public function __construct($id, $title, $impactStatement = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
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
}
