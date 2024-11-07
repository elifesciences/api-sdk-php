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

    public function count(): int
    {
        return count($this->subjects);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->results);
    }

    public function next(): void
    {
        next($this->subjects);
        next($this->results);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return current($this->subjects);
    }

    public function valid(): bool
    {
        return false !== $this->key();
    }

    public function rewind(): void
    {
        reset($this->subjects);
        reset($this->results);
    }
}
