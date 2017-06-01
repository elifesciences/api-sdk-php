<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;

final class Excerpt implements Block, HasContent
{
    private $content;
    private $cite;

    /**
     * @internal
     */
    public function __construct(Sequence $content, string $cite = null)
    {
        $this->content = $content;
        $this->cite = $cite;
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
    public function getCite()
    {
        return $this->cite;
    }
}
