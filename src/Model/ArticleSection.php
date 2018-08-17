<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class ArticleSection implements HasContent, HasDoi, HasId
{
    private $content;
    private $doi;
    private $id;

    /**
     * @internal
     */
    public function __construct(Sequence $content, string $doi = null, string $id = null)
    {
        $this->content = $content;
        $this->doi = $doi;
        $this->id = $id;
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
