<?php

namespace eLife\ApiSdk;

use BadMethodCallException;

trait ImmutableArrayAccess
{
    final public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Object is immutable');
    }

    final public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Object is immutable');
    }
}
