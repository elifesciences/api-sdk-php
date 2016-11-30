<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Coordinates;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\Place;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class PlaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Place('123', null, ['foo']);
        $withOut = new Place(null, null, ['foo']);

        $this->assertInstanceOf(HasId::class, $with);
        $this->assertSame('123', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_coordinates()
    {
        $coordinates = new Coordinates(123.45, 54.321);

        $with = new Place(null, $coordinates, ['foo']);
        $withOut = new Place(null, null, ['foo']);

        $this->assertEquals($coordinates, $with->getCoordinates());
        $this->assertNull($withOut->getCoordinates());
    }

    /**
     * @test
     */
    public function it_has_a_name()
    {
        $place = new Place(null, null, ['foo']);

        $this->assertEquals(['foo'], $place->getName());
    }

    /**
     * @test
     */
    public function it_may_have_an_address()
    {
        $address = Builder::dummy(Address::class);

        $with = new Place(null, null, ['foo'], $address);
        $withOut = new Place(null, null, ['foo']);

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

        $withAddress = new Place(null, null, ['foo', 'bar'], $address);
        $withOutAddress = new Place(null, null, ['foo']);

        $this->assertSame('foo, bar, baz, qux', $withAddress->toString());
        $this->assertSame('foo', $withOutAddress->toString());
    }
}
