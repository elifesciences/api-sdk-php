<?php

namespace eLife\ApiSdk;

use Countable;
use GuzzleHttp\Promise\PromiseInterface;
use Traversable;

interface Collection extends Countable, Traversable
{
    public function slice(int $offset, int $length = null) : Collection;

    public function map(callable $callback) : Collection;

    public function filter(callable $callback) : Collection;

    public function reduce(callable $callback, $initial = null) : PromiseInterface;

    public function sort(callable $callback) : Collection;

    public function toArray() : array;
}
