<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class SearchSubjects implements Iterator
{
    private $subjects;
    private $counters;
    
    public function __construct(array $subjects, array $counters)
    {
        $this->subjects = $subjects;
        $this->counters = $counters;
    }

    public function current()
    {
        return current($this->counters);
    }

    public function next()
    {
        next($this->subjects);
        next($this->counters);
    }

    public function key()
    {
        return current($this->subjects);
    }

    public function valid()
    {
        return $this->key() !== false;
    }

    public function rewind()
    {
        reset($this->subjects);
        reset($this->counters);
    }
}
