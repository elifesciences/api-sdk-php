<?php

namespace eLife\ApiSdk\Collection;

use eLife\ApiSdk\CanBeCounted;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\ImmutableArrayAccess;
use GuzzleHttp\Promise\PromiseInterface;
use IteratorAggregate;
use LogicException;
use Traversable;

final class PromiseSequence implements IteratorAggregate, Sequence, PromiseInterface
{
    use CanBeCounted;
    use ImmutableArrayAccess;

    private $promise;

    public function __construct(PromiseInterface $promise)
    {
        $this->promise = $promise->then(function ($value) {
            if ($value instanceof Sequence) {
                return $value;
            }

            if ($value instanceof Traversable) {
                $value = iterator_to_array($value);
            }

            return new ArraySequence((array) $value);
        });
    }

    public function getIterator() : Traversable
    {
        return $this->wait();
    }

    public function count() : int
    {
        return $this->wait()->count();
    }

    public function toArray() : array
    {
        return $this->wait()->toArray();
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        return new self(
            $this->then(function (Sequence $collection) use ($offset, $length) {
                return $collection->slice($offset, $length);
            })
        );
    }

    public function map(callable $callback) : Sequence
    {
        return new self(
            $this->then(function (Sequence $collection) use ($callback) {
                return $collection->map($callback);
            })
        );
    }

    public function filter(callable $callback = null) : Collection
    {
        return new self(
            $this->then(function (Sequence $collection) use ($callback) {
                return $collection->filter($callback);
            })
        );
    }

    public function reduce(callable $callback, $initial = null) : PromiseInterface
    {
        return $this->then(function (Sequence $collection) use ($callback, $initial) {
            return $collection->reduce($callback, $initial);
        });
    }

    public function sort(callable $callback) : Sequence
    {
        return new self(
            $this->then(function (Sequence $collection) use ($callback) {
                return $collection->sort($callback);
            })
        );
    }

    public function reverse() : Sequence
    {
        return new self(
            $this->then(function (Sequence $collection) {
                return $collection->reverse();
            })
        );
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null) : PromiseInterface
    {
        return $this->promise->then($onFulfilled, $onRejected);
    }

    public function otherwise(callable $onRejected) : PromiseInterface
    {
        return $this->promise->otherwise($onRejected);
    }

    public function getState() : string
    {
        return $this->promise->getState();
    }

    public function resolve($value)
    {
        throw new LogicException('Cannot resolve a PromiseSequence');
    }

    public function reject($reason)
    {
        throw new LogicException('Cannot reject a PromiseSequence');
    }

    public function cancel()
    {
        throw new LogicException('Cannot cancel a PromiseSequence');
    }

    public function wait($unwrap = true)
    {
        return $this->promise->wait($unwrap);
    }

    public function offsetExists($offset) : bool
    {
        return $this->wait()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->wait()->offsetGet($offset);
    }
}
