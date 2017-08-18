<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiSdk\Model\Identifier;

trait Contains
{
    private $containingQuery = [];

    final public function containing(Identifier ...$items) : self
    {
        $clone = clone $this;

        $clone->containingQuery = array_unique(array_merge($this->containingQuery, array_map('strval', $items)));

        if ($clone->containingQuery !== $this->containingQuery) {
            $clone->count = null;
        }

        return $clone;
    }
}
