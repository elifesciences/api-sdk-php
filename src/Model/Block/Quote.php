<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Quote implements Block
{
    private $text;
    private $cite;

    /**
     * @internal
     */
    public function __construct(array $text, string $cite = null)
    {
        $this->text = $text;
        $this->cite = $cite;
    }

    /**
     * @return Block[]
     */
    public function getText() : array
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function getCite()
    {
        return $this->cite;
    }
}
