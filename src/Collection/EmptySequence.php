<?php

namespace eLife\ApiSdk\Collection;

use ArrayIterator;
use eLife\ApiSdk\CanBeCounted;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\ImmutableArrayAccess;
use IteratorAggregate;
use Traversable;

final class EmptySequence implements IteratorAggregate, Sequence
{
    use CanBeCounted;
    use ImmutableArrayAccess;

    public function getIterator() : Traversable
    {
        return new ArrayIterator([]);
    }

    public function count() : int
    {
        return 0;
    }

    public function toArray() : array
    {
        return [];
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        return $this;
    }

    public function map(callable $callback) : Sequence
    {
        return $this;
    }

    public function filter(callable $callback = null) : Collection
    {
        return $this;
    }

    public function reduce(callable $callback, $initial = null)
    {
        return $initial;
    }

    public function sort(callable $callback = null) : Sequence
    {
        return $this;
    }

    public function reverse() : Sequence
    {
        return $this;
    }

    public function offsetExists($offset) : bool
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return null;
    }
}
