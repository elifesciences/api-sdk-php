<?php

namespace eLife\ApiSdk;

use Countable;
use Traversable;

interface Collection extends Countable, Traversable
{
    public function filter(callable $callback) : Collection;

    public function toArray() : array;
}
