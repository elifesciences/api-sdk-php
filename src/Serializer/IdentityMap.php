<?php

namespace eLife\ApiSdk\Serializer;

use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\all;

/**
 * As in http://www.martinfowler.com/eaaCatalog/identityMap.html.
 */
final class IdentityMap
{
    private $contents = [];

    public function reset($id = null) : self
    {
        if ($id) {
            $this->contents[$id] = null;
        } else {
            $this->contents = [];
        }

        return $this;
    }

    public function has($id) : bool
    {
        // on null, this must return false
        return isset($this->contents[$id]);
    }

    /**
     * @return PromiseInterface|null
     */
    public function get($id)
    {
        return $this->contents[$id];
    }

    public function fillMissingWith(callable $load) : self
    {
        foreach ($this->contents as $id => $promise) {
            if (null === $promise) {
                $this->contents[$id] = $load($id);
            }
        }

        return $this;
    }

    public function waitForAll() : array
    {
        return all($this->contents)->wait();
    }
}
