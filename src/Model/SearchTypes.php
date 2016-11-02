<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class SearchTypes implements IteratorAggregate
{
    private $typeToCounter;
    
    public function __construct($typeToCounter)
    {
        $this->typeToCounter = $typeToCounter;
    }

    public function getIterator() : Iterator
    {
        return new ArrayIterator($this->typeToCounter);
    }
}
