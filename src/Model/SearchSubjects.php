<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use IteratorAggregate;

class SearchSubjects implements IteratorAggregate
{
    private $c;
    
    public function __construct($c)
    {
        $this->c = $c;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->c);
    }
}
