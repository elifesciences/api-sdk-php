<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Section implements Block
{
    private $title;
    private $content;

    /**
     * @internal
     */
    public function __construct(string $title, array $content)
    {
        $this->title = $title;
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
     * @return Block[]
     */
    public function getContent() : array
    {
        return $this->content;
    }
}
