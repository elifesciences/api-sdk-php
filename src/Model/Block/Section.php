<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;

final class Section implements Block, HasContent, HasId
{
    private $title;
    private $id;
    private $content;

    /**
     * @internal
     */
    public function __construct(string $title, string $id = null, Sequence $content)
    {
        $this->title = $title;
        $this->id = $id;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }
}
