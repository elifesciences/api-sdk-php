<?php

namespace eLife\ApiSdk;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\FulfilledPromise;

trait ArrayFromIterator
{
    final public function map(callable $callback) : Sequence
    {
        return $this->all()->map($callback);
    }

    final public function filter(callable $callback = null) : Collection
    {
        return $this->all()->filter($callback);
    }

    final public function reduce(callable $callback, $initial = null)
    {
        return $this->all()->reduce($callback, $initial);
    }

    final public function sort(callable $callback = null) : Sequence
    {
        return $this->all()->sort($callback);
    }

    final public function toArray() : array
    {
        $array = [];

        foreach ($this as $item) {
            $array[] = $item;
        }

        return $array;
    }

    final private function all() : PromiseSequence
    {
        return new PromiseSequence(
            new FulfilledPromise($this->toArray())
        );
    }
}
