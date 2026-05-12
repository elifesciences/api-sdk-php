<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\YouTube;
use eLife\ApiSdk\Model\HasId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class YouTubeTest extends TestCase
{
    #[Test]
    public function it_is_a_block()
    {
        $video = new YouTube('foo', null, new EmptySequence(), 300, 200);

        $this->assertInstanceOf(Block::class, $video);
    }

    #[Test]
    public function it_has_an_id()
    {
        $video = new YouTube('foo', null, new EmptySequence(), 300, 200);

        $this->assertInstanceOf(HasId::class, $video);
        $this->assertSame('foo', $video->getId());
    }

    #[Test]
    public function it_may_have_a_title()
    {
        $with = new YouTube('foo', 'title', new EmptySequence(), 300, 200);
        $withOut = new YouTube('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    #[Test]
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new YouTube('foo', null, $caption, 300, 200);
        $withOut = new YouTube('foo', null, new EmptySequence(), 300, 200);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    #[Test]
    public function it_has_a_width()
    {
        $video = new YouTube('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame(300, $video->getWidth());
    }

    #[Test]
    public function it_has_a_height()
    {
        $video = new YouTube('foo', null, new EmptySequence(), 300, 200);

        $this->assertSame(200, $video->getHeight());
    }
}
