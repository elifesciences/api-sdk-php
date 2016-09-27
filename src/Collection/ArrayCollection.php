<?php

namespace eLife\ApiSdk\Collection;

use ArrayIterator;
use eLife\ApiSdk\Collection;
use GuzzleHttp\Promise\PromiseInterface;
use IteratorAggregate;
use Traversable;
use function GuzzleHttp\Promise\promise_for;

final class ArrayCollection implements IteratorAggregate, Collection
{
    private $array;

    public function __construct(array $array)
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

    public function slice(int $offset, int $length = null) : Collection
    {
        return new self(array_slice($this->array, $offset, $length));
    }

    public function map(callable $callback) : Collection
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

    public function sort(callable $callback) : Collection
    {
        $clone = clone $this;

        usort($clone->array, $callback);

        return $clone;
    }

    public function reverse() : Collection
    {
        return new self(array_reverse($this->array));
    }

    public function jsonSerialize()
    {
        return $this->array;
    }
}
