<?php

namespace eLife\ApiSdk\Serializer;

use ArrayIterator;
use IteratorAggregate;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\all;

/**
 * As in http://www.martinfowler.com/eaaCatalog/identityMap.html
 */
class IdentityMap implements IteratorAggregate
{
    private $contents = [];

    public function reset($id)
    {
        $this->contents[$id] = null;
        return $this; 
    }

    public function has($id)
    {
        return array_key_exists($id, $this->contents); 
    }

    public function get($id)
    {
        return $this->contents[$id];
    }

    public function put($id, PromiseInterface $promise)
    {
        $this->contents[$id] = $promise;        
        return $id;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->contents);
    }

    public function waitForAll()
    {
        return all($this->contents)->wait();
    }
}
