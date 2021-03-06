<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class Appendix implements HasContent, HasDoi, HasId
{
    private $id;
    private $title;
    private $content;
    private $doi;

    /**
     * @internal
     */
    public function __construct(string $id, string $title, Sequence $content, string $doi = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->doi = $doi;
    }

    public function getId() : string
    {
        return $this->id;
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
}
