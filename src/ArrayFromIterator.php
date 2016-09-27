<?php

namespace eLife\ApiSdk;

use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

trait ArrayFromIterator
{
    final public function map(callable $callback) : Collection
    {
        return $this->all()->map($callback);
    }

    final public function filter(callable $callback) : Collection
    {
        return $this->all()->filter($callback);
    }

    final public function reduce(callable $callback, $initial = null) : PromiseInterface
    {
        return $this->all()->reduce($callback, $initial);
    }

    final public function sort(callable $callback) : Collection
    {
        return $this->all()->sort($callback);
    }

    final public function jsonSerialize()
    {
        return $this->toArray();
    }

    final public function toArray() : array
    {
        $array = [];

        foreach ($this as $item) {
            $array[] = $item;
        }

        return $array;
    }

    final private function all() : PromiseCollection
    {
        return new PromiseCollection(
            $promise = new Promise(
                function () use (&$promise) {
                    $promise->resolve(new ArrayCollection($this->toArray()));
                }
            )
        );
    }
}
