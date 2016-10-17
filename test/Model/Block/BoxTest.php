<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use PHPUnit_Framework_TestCase;

final class BoxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $box = new Box(null, null, null, 'title', []);

        $this->assertInstanceOf(Block::class, $box);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new Box('10.1000/182', null, null, 'title', []);
        $withOut = new Box(null, null, null, 'title', []);

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Box(null, 'id', null, 'title', []);
        $withOut = new Box(null, null, null, 'title', []);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new Box(null, null, 'label', 'title', []);
        $withOut = new Box(null, null, null, 'title', []);

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $box = new Box(null, null, null, 'title', []);

        $this->assertSame('title', $box->getTitle());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Box(null, null, null, 'sub-title', [])];
        $box = new Box(null, null, null, 'title', $content);

        $this->assertEquals($content, $box->getContent());
    }
}
