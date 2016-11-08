<?php

namespace eLife\ApiSdk\Collection;

use ArrayIterator;
use eLife\ApiSdk\CanBeCounted;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\ImmutableArrayAccess;
use GuzzleHttp\Promise\PromiseInterface;
use IteratorAggregate;
use Traversable;
use function GuzzleHttp\Promise\promise_for;

final class ArraySequence implements IteratorAggregate, Sequence
{
    use CanBeCounted;
    use ImmutableArrayAccess;

    private $array;

    public function __construct(array $array = [])
    {
        $this->array = array_values($array);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->array);
    }

    public function count() : int
    {
        return count($this->array);
    }

    public function toArray() : array
    {
        return $this->array;
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        return new self(array_slice($this->array, $offset, $length));
    }

    public function map(callable $callback) : Sequence
    {
        return new self(array_map($callback, $this->array));
    }

    public function filter(callable $callback) : Collection
    {
        return new self(array_filter($this->array, $callback));
    }

    public function reduce(callable $callback, $initial = null) : PromiseInterface
    {
        return promise_for(array_reduce($this->array, $callback, $initial));
    }

    public function sort(callable $callback = null) : Sequence
    {
        $clone = clone $this;

        if (null === $callback) {
            sort($clone->array);
        } else {
            usort($clone->array, $callback);
        }

        return $clone;
    }

    public function reverse() : Sequence
    {
        return new self(array_reverse($this->array));
    }

    public function offsetExists($offset) : bool
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->array[$offset])) {
            return null;
        }

        return $this->array[$offset];
    }
}
