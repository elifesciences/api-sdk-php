<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

class SearchTypes implements IteratorAggregate, Countable
{
    private $typesToResults;

    public function __construct($typesToResults)
    {
        $this->typesToResults = $typesToResults;
    }

    public function count()
    {
        return count($this->typesToResults);
    }

    public function getIterator() : Iterator
    {
        return new ArrayIterator($this->typesToResults);
    }
}
