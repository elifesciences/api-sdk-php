<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Address;
use PHPUnit_Framework_TestCase;

final class AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_formatted_address()
    {
        $address = new Address(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $address->getFormatted());
    }

    /**
     * @test
     */
    public function it_may_have_a_street_address()
    {
        $with = new Address(['foo', 'bar'], ['foo', 'bar']);
        $withOut = new Address(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $with->getStreetAddress());
        $this->assertEmpty($withOut->getStreetAddress());
    }

    /**
     * @test
     */
    public function it_may_have_a_locality()
    {
        $with = new Address(['foo', 'bar'], [], ['foo', 'bar']);
        $withOut = new Address(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $with->getLocality());
        $this->assertEmpty($withOut->getLocality());
    }

    /**
     * @test
     */
    public function it_may_have_an_area()
    {
        $with = new Address(['foo', 'bar'], [], [], ['foo', 'bar']);
        $withOut = new Address(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $with->getArea());
        $this->assertEmpty($withOut->getArea());
    }

    /**
     * @test
     */
    public function it_may_have_a_country()
    {
        $with = new Address(['foo'], [], [], [], 'foo');
        $withOut = new Address(['foo']);

        $this->assertSame('foo', $with->getCountry());
        $this->assertNull($withOut->getCountry());
    }

    /**
     * @test
     */
    public function it_may_have_a_postal_code()
    {
        $with = new Address(['foo'], [], [], [], null, 'foo');
        $withOut = new Address(['foo']);

        $this->assertSame('foo', $with->getPostalCode());
        $this->assertNull($withOut->getPostalCode());
    }
}
