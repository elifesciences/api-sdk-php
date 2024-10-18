<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

final class SearchTypes implements IteratorAggregate, Countable
{
    private $typesToResults;

    /**
     * @internal
     */
    public function __construct(array $typesToResults)
    {
        $this->typesToResults = $typesToResults;
    }

    public function count(): int
    {
        return count($this->typesToResults);
    }

    public function getIterator() : Iterator
    {
        return new ArrayIterator($this->typesToResults);
    }
}
