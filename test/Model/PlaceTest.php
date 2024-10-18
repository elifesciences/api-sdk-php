<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Place;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class PlaceTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_name()
    {
        $place = new Place(['foo']);

        $this->assertEquals(['foo'], $place->getName());
    }

    /**
     * @test
     */
    public function it_may_have_an_address()
    {
        $address = Builder::dummy(Address::class);

        $with = new Place(['foo'], $address);
        $withOut = new Place(['foo']);

        $this->assertEquals($address, $with->getAddress());
        $this->assertNull($withOut->getAddress());
    }

    /**
     * @test
     */
    public function it_casts_to_a_string()
    {
        $address = Builder::for(Address::class)
            ->withFormatted($sequence = new ArraySequence(['baz', 'qux']))
            ->__invoke();

        $withAddress = new Place(['foo', 'bar'], $address);
        $withOutAddress = new Place(['foo']);

        $this->assertSame('foo, bar, baz, qux', $withAddress->toString());
        $this->assertSame('foo', $withOutAddress->toString());
    }
}
