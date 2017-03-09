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
    private $locked = false;

    public function reset($id = null) : self
    {
        if ($this->locked) {
            die("Cannot reset contents while fillMissingWith() is operating");
        }
        if ($id) {
            $this->contents[$id] = null;
        } else {
            $this->contents = [];
        }
        //echo "Resetting $id", PHP_EOL;

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
        $this->locked = true;
        var_Dump("starting fillMissingWith()");
        $iterations = 0;
        $loadings = 0;
        $noLoadings = 0;
        foreach ($this->contents as $id => $promise) {
            if (null === $promise) {
                $promise = $load($id);
                $loadings++;
                if (!$promise) {
                    throw new \RuntimeException("Promises cannot be null. Id: ",$id);
                }
                $this->contents[$id] = $promise;
            } else {
                $noLoadings++;
                var_dump(get_class($promise));
            }
            $iterations++;
        }
        $count = count(array_filter($this->contents, function($v) { return !$v; }));
        if ($count) {
            throw new \RuntimeException("After fillMissingWith() ($iterations iterations, $loadings loadings, $noLoadings no loadings), $count in \$this->contents are falsy");
        }
        $this->locked = false;

        return $this;
    }

    public function waitForAll() : array
    {
        return all($this->contents)->wait();
    }
}
