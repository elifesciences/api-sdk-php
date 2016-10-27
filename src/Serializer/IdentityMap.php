<?php

namespace eLife\ApiSdk\Serializer;

use ArrayIterator;
use IteratorAggregate;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\all;

/**
 * As in http://www.martinfowler.com/eaaCatalog/identityMap.html
 */
final class IdentityMap
{
    private $contents = [];

    public function reset($id) : self
    {
        $this->contents[$id] = null;
        return $this; 
    }

    public function has($id) : bool
    {
        return array_key_exists($id, $this->contents); 
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
                $this->contents[$id] =  $load($id);
            }
        }
        return $this;
    }

    public function waitForAll()
    {
        return all($this->contents)->wait();
    }
}
