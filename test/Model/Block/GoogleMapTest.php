<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use eLife\ApiSdk\Model\HasId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GoogleMapTest extends TestCase
{
    #[Test]
    public function it_is_a_block()
    {
        $googleMap = new GoogleMap('foo', 'title');

        $this->assertInstanceOf(Block::class, $googleMap);
    }

    #[Test]
    public function it_has_an_id()
    {
        $googleMap = new GoogleMap('foo', 'title');

        $this->assertInstanceOf(HasId::class, $googleMap);
        $this->assertSame('foo', $googleMap->getId());
    }

    #[Test]
    public function it_has_a_title()
    {
        $googleMap = new GoogleMap('foo', 'title');

        $this->assertSame('title', $googleMap->getTitle());
    }
}
