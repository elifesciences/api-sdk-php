<?php

namespace eLife\ApiSdk\Model\Collection;

use eLife\ApiSdk\CanBeCounted;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\ImmutableArrayAccess;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use IteratorAggregate;
use IteratorIterator;
use Traversable;

final class FlattenedBlockSequence implements IteratorAggregate, Sequence
{
    use CanBeCounted;
    use ImmutableArrayAccess;

    private $sequence;

    public function __construct(Sequence $sequence)
    {
        $this->sequence = new ArraySequence($sequence->reduce(function (array $array, Block $item) {
            return $this->flatten($array, $item);
        }, []));
    }

    public function getIterator() : Traversable
    {
        return new IteratorIterator($this->sequence);
    }

    public function count() : int
    {
        return $this->sequence->count();
    }

    public function toArray() : array
    {
        return $this->sequence->toArray();
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        return new self($this->sequence->slice($offset, $length));
    }

    public function map(callable $callback) : Sequence
    {
        return new self($this->sequence->map($callback));
    }

    public function filter(callable $callback = null) : Collection
    {
        return new self($this->sequence->filter($callback));
    }

    public function reduce(callable $callback, $initial = null)
    {
        return $this->sequence->reduce($callback, $initial);
    }

    public function sort(callable $callback = null) : Sequence
    {
        return new self($this->sequence->sort($callback));
    }

    public function reverse() : Sequence
    {
        return new self($this->sequence->reverse());
    }

    public function offsetExists($offset) : bool
    {
        return $this->sequence->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->sequence->offsetGet($offset);
    }

    private function flatten(array $array, Block $block) : array
    {
        $array[] = $block;

        if ($block instanceof HasContent) {
            $array = $block->getContent()->reduce(function (array $array, Block $item) {
                return $this->flatten($array, $item);
            }, $array);
        }

        return $array;
    }
}
