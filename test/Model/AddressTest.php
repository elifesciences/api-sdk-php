<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_formatted_address()
    {
        $address = Builder::for(Address::class)
            ->withFormatted($sequence = new ArraySequence(['foo', 'bar']))
            ->__invoke();

        $this->assertSame($sequence, $address->getFormatted());
    }

    /**
     * @test
     */
    public function it_may_have_a_street_address()
    {
        $with = Builder::for(Address::class)
            ->withStreetAddress($sequence = new ArraySequence(['foo', 'bar']))
            ->__invoke();
        $withOut = Builder::for(Address::class)
            ->withStreetAddress(null)
            ->__invoke();

        $this->assertSame($sequence, $with->getStreetAddress());
        $this->assertEmpty($withOut->getStreetAddress());
    }

    /**
     * @test
     */
    public function it_may_have_a_locality()
    {
        $with = Builder::for(Address::class)
            ->withLocality($sequence = new ArraySequence(['foo', 'bar']))
            ->__invoke();
        $withOut = Builder::for(Address::class)
            ->withLocality(null)
            ->__invoke();

        $this->assertSame($sequence, $with->getLocality());
        $this->assertEmpty($withOut->getLocality());
    }

    /**
     * @test
     */
    public function it_may_have_an_area()
    {
        $with = Builder::for(Address::class)
            ->withArea($sequence = new ArraySequence(['foo', 'bar']))
            ->__invoke();
        $withOut = Builder::for(Address::class)
            ->withArea(null)
            ->__invoke();

        $this->assertSame($sequence, $with->getArea());
        $this->assertEmpty($withOut->getArea());
    }

    /**
     * @test
     */
    public function it_may_have_a_country()
    {
        $with = Builder::for(Address::class)
            ->withCountry('foo')
            ->__invoke();
        $withOut = Builder::for(Address::class)
            ->withCountry(null)
            ->__invoke();

        $this->assertSame('foo', $with->getCountry());
        $this->assertNull($withOut->getCountry());
    }

    /**
     * @test
     */
    public function it_may_have_a_postal_code()
    {
        $with = Builder::for(Address::class)
            ->withPostalCode('foo')
            ->__invoke();
        $withOut = Builder::for(Address::class)
            ->withPostalCode(null)
            ->__invoke();

        $this->assertSame('foo', $with->getPostalCode());
        $this->assertNull($withOut->getPostalCode());
    }
}
