<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class PublicReview implements HasContent, HasDoi, HasId
{
    private $title;
    private $content;
    private $doi;
    private $id;

    /**
     * @internal
     */
    public function __construct(string $title, Sequence $content, string $doi = null, string $id = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->doi = $doi;
        $this->id = $id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }
}
