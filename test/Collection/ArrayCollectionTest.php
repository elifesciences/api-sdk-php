<?php

namespace test\eLife\ApiSdk\Collection;

use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use PHPUnit_Framework_TestCase;

final class ArrayCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_collection()
    {
        $collection = new ArrayCollection([]);

        $this->assertInstanceOf(Collection::class, $collection);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        foreach ($collection as $i => $element) {
            $this->assertSame($i + 1, $element);
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        $this->assertSame(5, $collection->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $array = [1, 2, 3, 4, 5];

        $collection = new ArrayCollection($array);

        $this->assertSame($array, $collection->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected)
    {
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        $this->assertSame($expected, $collection->slice($offset, $length)->toArray());
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
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        $map = function (int $number) {
            return $number * 100;
        };

        $this->assertSame([100, 200, 300, 400, 500], $collection->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        $filter = function (int $number) {
            return $number > 3;
        };

        $this->assertSame([4, 5], $collection->filter($filter)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

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
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        $sort = function (int $a, int $b) {
            return $b <=> $a;
        };

        $this->assertSame([5, 4, 3, 2, 1], $collection->sort($sort)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);

        $this->assertSame([5, 4, 3, 2, 1], $collection->reverse()->toArray());
    }
}
