<?php

namespace eLife\ApiSdk;

use eLife\ApiClient\Exception\BadResponse;

trait SlicedArrayAccess
{
    use CanBeSliced;
    use ImmutableArrayAccess;

    public function offsetExists($offset) : bool
    {
        return null !== $this->offsetGet($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        try {
            $slice = $this->slice($offset, 1)->toArray();
        } catch (BadResponse $e) {
            if (404 === $e->getResponse()->getStatusCode()) {
                return null;
            }

            throw $e;
        }

        if (empty($slice)) {
            return null;
        }

        return $slice[0];
    }
}
