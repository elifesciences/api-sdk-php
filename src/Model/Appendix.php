<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class Appendix
{
    private $id;
    private $title;
    private $content;
    private $doi;

    /**
     * @internal
     */
    public function __construct(string $id, string $title, Sequence $content, string $doi)
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

    public function getDoi() : string
    {
        return $this->doi;
    }
}
