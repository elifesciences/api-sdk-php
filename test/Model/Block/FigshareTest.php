<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figshare;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class FigshareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $figshare = new Figshare('foo', null, new EmptySequence(), 300, 200);

        $this->assertInstanceOf(Block::class, $figshare);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $figshare = new Figshare('foo', null, new EmptySequence(), 300, 200);

        $this->assertInstanceOf(HasId::class, $figshare);
        $this->assertSame('foo', $figshare->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new Figshare('foo', 'title', new EmptySequence(), 300, 200);
        $withOut = new Figshare('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new Figshare('foo', null, $caption, 300, 200);
        $withOut = new Figshare('foo', null, new EmptySequence(), 300, 200);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $figshare = new Figshare('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame(300, $figshare->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $figshare = new Figshare('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame(200, $figshare->getHeight());
    }
}
