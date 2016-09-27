<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Section implements Block
{
    private $title;
    private $id;
    private $content;

    /**
     * @internal
     */
    public function __construct(string $title, string $id = null, array $content)
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
     * @return Block[]
     */
    public function getContent() : array
    {
        return $this->content;
    }
}
