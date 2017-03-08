<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Button implements Block
{
    private $text;
    private $uri;

    /**
     * @internal
     */
    public function __construct(string $text, string $uri)
    {
        $this->text = $text;
        $this->uri = $uri;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
