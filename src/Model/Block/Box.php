<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Box implements Block
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
        array $content
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

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return Block[]
     */
    public function getContent() : array
    {
        return $this->content;
    }
}
