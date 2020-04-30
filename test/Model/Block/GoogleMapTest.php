<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class GoogleMapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $googleMap = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        $this->assertInstanceOf(Block::class, $googleMap);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $googleMap = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        $this->assertInstanceOf(HasId::class, $googleMap);
        $this->assertSame('foo', $googleMap->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new GoogleMap('foo', 'title', new EmptySequence(), 300, 200);
        $withOut = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new GoogleMap('foo', null, $caption, 300, 200);
        $withOut = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $googleMap = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame(300, $googleMap->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $googleMap = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame(200, $googleMap->getHeight());
    }
}
