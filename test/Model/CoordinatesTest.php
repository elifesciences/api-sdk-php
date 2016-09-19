<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Coordinates;
use PHPUnit_Framework_TestCase;

final class CoordinatesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_latitude()
    {
        $coordinates = new Coordinates(123.45, 54.321);

        $this->assertSame(123.45, $coordinates->getLatitude());
    }

    /**
     * @test
     */
    public function it_has_a_longitude()
    {
        $coordinates = new Coordinates(123.45, 54.321);

        $this->assertSame(54.321, $coordinates->getLongitude());
    }
}
