<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class GoogleMapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $googleMap = new GoogleMap('foo', 'title');

        $this->assertInstanceOf(Block::class, $googleMap);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $googleMap = new GoogleMap('foo', 'title');

        $this->assertInstanceOf(HasId::class, $googleMap);
        $this->assertSame('foo', $googleMap->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $googleMap = new GoogleMap('foo', 'title');

        $this->assertSame('title', $googleMap->getTitle());
    }
}
