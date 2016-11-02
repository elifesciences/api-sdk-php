<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

class SearchTypes implements IteratorAggregate, Countable
{
    private $typeToCounter;
    
    public function __construct($typeToCounter)
    {
        $this->typeToCounter = $typeToCounter;
    }

    public function count()
    {
        return count($this->typeToCounter);
    }

    public function getIterator() : Iterator
    {
        return new ArrayIterator($this->typeToCounter);
    }
}
