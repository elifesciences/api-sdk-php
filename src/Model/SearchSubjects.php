<?php

namespace eLife\ApiSdk\Model;

use Countable;
use Iterator;

final class SearchSubjects implements Iterator, Countable
{
    private $subjects;
    private $results;

    /**
     * @internal
     */
    public function __construct(array $subjects, array $results)
    {
        $this->subjects = $subjects;
        $this->results = $results;
    }

    public function count()
    {
        return count($this->subjects);
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
