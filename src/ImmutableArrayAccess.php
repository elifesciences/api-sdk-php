<?php

namespace eLife\ApiSdk;

use BadMethodCallException;

trait ImmutableArrayAccess
{
    final public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Object is immutable');
    }

    final public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Object is immutable');
    }
}
