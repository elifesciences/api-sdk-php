<?php

namespace eLife\ApiSdk\Model;

use Iterator;

class SearchSubjects implements Iterator
{
    private $subjects;
    private $results;

    public function __construct(array $subjects, array $results)
    {
        $this->subjects = $subjects;
        $this->results = $results;
    }

    public function current()
    {
        return current($this->results);
    }

    public function next()
    {
        next($this->subjects);
        next($this->results);
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
        reset($this->results);
    }
}
