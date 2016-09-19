<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Coordinates;
use eLife\ApiSdk\Model\Place;
use PHPUnit_Framework_TestCase;

final class PlaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Place('123', null, ['foo']);
        $withOut = new Place(null, null, ['foo']);

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
        $address = new Address(['bar']);

        $with = new Place(null, null, ['foo'], $address);
        $withOut = new Place(null, null, ['foo']);

        $this->assertEquals($address, $with->getAddress());
        $this->assertNull($withOut->getAddress());
    }
}
