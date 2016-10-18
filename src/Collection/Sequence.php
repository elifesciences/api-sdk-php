<?php

namespace eLife\ApiSdk\Collection;

use eLife\ApiSdk\Collection;
use GuzzleHttp\Promise\PromiseInterface;

interface Sequence extends Collection
{
    public function map(callable $callback) : Sequence;

    public function slice(int $offset, int $length = null) : Sequence;

    /**
     * @return Sequence
     */
    public function filter(callable $callback) : Collection;

    public function reduce(callable $callback, $initial = null) : PromiseInterface;

    public function sort(callable $callback) : Sequence;

    public function reverse() : Sequence;
}
