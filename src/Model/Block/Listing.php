<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Listing implements Block
{
    private $ordered;
    private $items;

    /**
     * @internal
     */
    public function __construct(bool $ordered, array $items)
    {
        $this->ordered = $ordered;
        $this->items = $items;
    }

    public function isOrdered() : bool
    {
        return $this->ordered;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
