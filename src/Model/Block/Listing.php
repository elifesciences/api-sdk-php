<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;

final class Listing implements Block
{
    const PREFIX_ALPHA_LOWER = 'alpha-lower';
    const PREFIX_ALPHA_UPPER = 'alpha-upper';
    const PREFIX_BULLET = 'bullet';
    const PREFIX_NONE = 'none';
    const PREFIX_NUMBER = 'number';
    const PREFIX_ROMAN_LOWER = 'roman-lower';
    const PREFIX_ROMAN_UPPER = 'roman-upper';

    private $prefix;
    private $items;

    /**
     * @internal
     */
    public function __construct(string $prefix, Sequence $items)
    {
        $this->prefix = $prefix;
        $this->items = $items;
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function getItems() : Sequence
    {
        return $this->items;
    }
}
