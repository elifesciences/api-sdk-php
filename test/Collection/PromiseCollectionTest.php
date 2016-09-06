<?php

namespace test\eLife\ApiSdk\Collection;

use ArrayObject;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use Exception;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use LogicException;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class PromiseCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_collection()
    {
        $collection = new PromiseCollection(promise_for([]));

        $this->assertInstanceOf(Collection::class, $collection);
    }

    /**
     * @test
     */
    public function it_is_a_promise()
    {
        $collection = new PromiseCollection(promise_for([]));

        $this->assertInstanceOf(PromiseInterface::class, $collection);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $collection = new PromiseCollection(promise_for([1, 2, 3, 4, 5]));

        foreach ($collection as $i => $element) {
            $this->assertSame($i + 1, $element);
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $collection = new PromiseCollection(promise_for([1, 2, 3, 4, 5]));

        $this->assertSame(5, $collection->count());
    }

    /**
     * @test
     * @dataProvider valueProvider
     */
    public function it_casts_to_an_array($value, array $expected)
    {
        $collection = new PromiseCollection(promise_for($value));

        $this->assertSame($expected, $collection->toArray());
    }

    public function valueProvider() : array
    {
        return [
            'array' => [['foo'], ['foo']],
            'collection' => [new ArrayCollection(['foo']), ['foo']],
            'traversable' => [new ArrayObject(['foo']), ['foo']],
            'string' => ['foo', ['foo']],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected)
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $sliced = $collection->slice($offset, $length);

        $promise->resolve([1, 2, 3, 4, 5]);

        $this->assertSame($expected, $sliced->toArray());
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [1, 1, [2]],
            'offset -2, no length' => [-2, null, [4, 5]],
            'offset 6, no length' => [6, null, []],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $map = function (int $number) {
            return $number * 100;
        };

        $mapped = $collection->map($map);

        $promise->resolve([1, 2, 3, 4, 5]);

        $this->assertSame([100, 200, 300, 400, 500], $mapped->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $filter = function (int $number) {
            return $number > 3;
        };

        $filtered = $collection->filter($filter);

        $promise->resolve([1, 2, 3, 4, 5]);

        $this->assertSame([4, 5], $filtered->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $collection = new PromiseCollection(promise_for([1, 2, 3, 4, 5]));

        $reduce = function (int $carry = null, int $number) {
            return $carry + $number;
        };

        $this->assertSame(115, $collection->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $collection = new PromiseCollection(promise_for([1, 2, 3, 4, 5]));

        $sort = function (int $a, int $b) {
            return $b <=> $a;
        };

        $sorted = $collection->sort($sort);

        $this->assertSame([5, 4, 3, 2, 1], $sorted->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_chained()
    {
        $collection = new PromiseCollection(promise_for([1, 2, 3, 4, 5]));

        $result = $collection->then(function () {
            return 'foo';
        });

        $this->assertSame('foo', $result->wait());
    }

    /**
     * @test
     */
    public function it_can_handle_exceptions()
    {
        $collection = new PromiseCollection(rejection_for('foo'));

        $result = $collection->otherwise(function () {
            return 'bar';
        });

        $this->assertSame('bar', $result->wait());
    }

    /**
     * @test
     */
    public function it_has_a_state()
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $this->assertSame(PromiseInterface::PENDING, $collection->getState());

        $promise->resolve(true);
        $collection->wait();

        $this->assertSame(PromiseInterface::FULFILLED, $collection->getState());
    }

    /**
     * @test
     */
    public function it_cannot_be_resolved()
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $this->expectException(LogicException::class);

        $collection->resolve([1, 2, 3, 4, 5]);
    }

    /**
     * @test
     */
    public function it_cannot_be_rejected()
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $this->expectException(LogicException::class);

        $collection->reject(new Exception('foo'));
    }

    /**
     * @test
     */
    public function it_cannot_be_cancelled()
    {
        $promise = new Promise();
        $collection = new PromiseCollection($promise);

        $this->expectException(LogicException::class);

        $collection->cancel();
    }

    /**
     * @test
     */
    public function it_can_be_waited_on()
    {
        $collection = new PromiseCollection(rejection_for('foo'));

        $this->assertNull($collection->wait(false));
    }

    /**
     * @test
     */
    public function it_can_be_unwrapped()
    {
        $collection = new PromiseCollection(promise_for([1, 2, 3, 4, 5]));

        $this->assertSame([1, 2, 3, 4, 5], $collection->wait()->toArray());
    }
}
