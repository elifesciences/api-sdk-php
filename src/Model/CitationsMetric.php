<?php

namespace eLife\ApiSdk\Model;

use ArrayIterator;
use eLife\ApiSdk\CanBeCounted;
use eLife\ApiSdk\Collection;
use IteratorAggregate;
use Traversable;

final class CitationsMetric implements IteratorAggregate, Collection
{
    use CanBeCounted;

    private $sources;

    /**
     * @internal
     */
    public function __construct(CitationsMetricSource ...$sources)
    {
        $this->sources = $sources;
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->sources);
    }

    public function count() : int
    {
        return count($this->sources);
    }

    public function toArray() : array
    {
        return $this->sources;
    }

    public function filter(callable $callback = null) : Collection
    {
        if (null === $callback) {
            return $this;
        }

        return new self(...array_filter($this->sources, $callback));
    }

    public function getHighest() : CitationsMetricSource
    {
        $highest = $this->sources[0];

        foreach ($this->sources as $source) {
            if ($source->getCitations() > $highest->getCitations()) {
                $highest = $source;
            }
        }

        return $highest;
    }
}
