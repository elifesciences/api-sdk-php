<?php

namespace test\eLife\ApiSdk\Collection;

use ArrayObject;
use BadMethodCallException;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use Exception;
use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use LogicException;
use PHPUnit\Framework\TestCase;

final class PromiseSequenceTest extends TestCase
{
    #[Test]
    public function it_is_a_sequence()
    {
        $collection = new PromiseSequence(Create::promiseFor([]));

        $this->assertInstanceOf(Sequence::class, $collection);
    }

    #[Test]
    public function it_is_a_promise()
    {
        $collection = new PromiseSequence(Create::promiseFor([]));

        $this->assertInstanceOf(PromiseInterface::class, $collection);
    }

    #[Test]
    public function it_can_be_traversed()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        foreach ($collection as $i => $element) {
            $this->assertSame($i + 1, $element);
        }
    }

    #[Test]
    public function it_can_be_counted()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(5, $collection->count());
    }

    #[Test]
    #[DataProvider('valueProvider')]
    public function it_casts_to_an_array($value, array $expected)
    {
        $collection = new PromiseSequence(Create::promiseFor($value));

        $this->assertSame($expected, $collection->toArray());
    }

    public static function valueProvider() : array
    {
        return [
            'array' => [['foo'], ['foo']],
            'collection' => [new ArraySequence(['foo']), ['foo']],
            'traversable' => [new ArrayObject(['foo']), ['foo']],
            'string' => ['foo', ['foo']],
        ];
    }

    #[Test]
    public function it_can_be_accessed_like_an_array()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $this->assertTrue(isset($collection[0]));
        $this->assertSame(1, $collection[0]);
        $this->assertFalse(isset($collection[5]));
        $this->assertSame(null, $collection[5]);
    }

    #[Test]
    public function it_is_an_immutable_array()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $this->expectException(BadMethodCallException::class);

        $collection[0] = 'foo';
    }

    #[Test]
    public function it_can_be_prepended()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $collection = $collection->prepend(-1, 0);

        $this->assertSame([-1, 0, 1, 2, 3, 4, 5], $collection->toArray());
    }

    #[Test]
    public function it_can_be_appended()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $collection = $collection->append(6, 7);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7], $collection->toArray());
    }

    #[Test]
    public function it_can_have_values_dropped()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $collection = $collection->drop(1, 3);

        $this->assertSame([1, 3, 5], $collection->toArray());
    }

    #[Test]
    public function it_can_have_values_inserted()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $collection = $collection->insert(2, 'foo', 'bar');

        $this->assertSame([1, 2, 'foo', 'bar', 3, 4, 5], $collection->toArray());
    }

    #[Test]
    public function it_can_have_a_value_set()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $collection = $collection->set(2, 'foo');

        $this->assertSame([1, 2, 'foo', 4, 5], $collection->toArray());
    }

    #[Test]
    #[DataProvider('sliceProvider')]
    public function it_can_be_sliced(int $offset, int $length = null, array $expected)
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $sliced = $collection->slice($offset, $length);

        $promise->resolve([1, 2, 3, 4, 5]);

        $this->assertSame($expected, $sliced->toArray());
    }

    public static function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [1, 1, [2]],
            'offset -2, no length' => [-2, null, [4, 5]],
            'offset 6, no length' => [6, null, []],
        ];
    }

    #[Test]
    public function it_can_be_mapped()
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $map = function (int $number) {
            return $number * 100;
        };

        $mapped = $collection->map($map);

        $promise->resolve([1, 2, 3, 4, 5]);

        $this->assertSame([100, 200, 300, 400, 500], $mapped->toArray());
    }

    #[Test]
    public function it_can_be_filtered()
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $filter = function (int $number) {
            return $number > 3;
        };

        $filtered = $collection->filter($filter);

        $promise->resolve([1, 2, 3, 4, 5]);

        $this->assertSame([4, 5], $filtered->toArray());
    }

    #[Test]
    public function it_can_be_reduced()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $reduce = function (int $carry = null, int $number) {
            return $carry + $number;
        };

        $this->assertSame(115, $collection->reduce($reduce, 100));
    }

    #[Test]
    public function it_can_be_flattened()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, new ArraySequence([2]), 3, 4, 5]));

        $this->assertSame([1, 2, 3, 4, 5], $collection->flatten()->toArray());
    }

    #[Test]
    public function it_can_be_sorted()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $sort = function (int $a, int $b) {
            return $b <=> $a;
        };

        $sorted = $collection->sort($sort);

        $this->assertSame([5, 4, 3, 2, 1], $sorted->toArray());
    }

    #[Test]
    public function it_can_be_reversed()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $this->assertSame([5, 4, 3, 2, 1], $collection->reverse()->toArray());
    }

    #[Test]
    public function it_can_be_chained()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $result = $collection->then(function () {
            return 'foo';
        });

        $this->assertSame('foo', $result->wait());
    }

    #[Test]
    public function it_can_handle_exceptions()
    {
        $collection = new PromiseSequence(Create::rejectionFor('foo'));

        $result = $collection->otherwise(function () {
            return 'bar';
        });

        $this->assertSame('bar', $result->wait());
    }

    #[Test]
    public function it_has_a_state()
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $this->assertSame(PromiseInterface::PENDING, $collection->getState());

        $promise->resolve(true);
        $collection->wait();

        $this->assertSame(PromiseInterface::FULFILLED, $collection->getState());
    }

    #[Test]
    public function it_cannot_be_resolved()
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $this->expectException(LogicException::class);

        $collection->resolve([1, 2, 3, 4, 5]);
    }

    #[Test]
    public function it_cannot_be_rejected()
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $this->expectException(LogicException::class);

        $collection->reject(new Exception('foo'));
    }

    #[Test]
    public function it_cannot_be_cancelled()
    {
        $promise = new Promise();
        $collection = new PromiseSequence($promise);

        $this->expectException(LogicException::class);

        $collection->cancel();
    }

    #[Test]
    public function it_can_be_waited_on()
    {
        $collection = new PromiseSequence(Create::rejectionFor('foo'));

        $this->assertNull($collection->wait(false));
    }

    #[Test]
    public function it_can_be_unwrapped()
    {
        $collection = new PromiseSequence(Create::promiseFor([1, 2, 3, 4, 5]));

        $this->assertSame([1, 2, 3, 4, 5], $collection->wait()->toArray());
    }
}
