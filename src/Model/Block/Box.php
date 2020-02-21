<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;

final class Box implements Block, HasContent, HasDoi, HasId
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $id = null,
        string $label = null,
        string $title,
        Sequence $content
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->content = $content;
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

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
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
}
