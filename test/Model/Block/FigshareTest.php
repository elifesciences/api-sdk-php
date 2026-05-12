<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figshare;
use eLife\ApiSdk\Model\HasId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FigshareTest extends TestCase
{
    #[Test]
    public function it_is_a_block()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertInstanceOf(Block::class, $figshare);
    }

    #[Test]
    public function it_has_an_id()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertInstanceOf(HasId::class, $figshare);
        $this->assertSame('foo', $figshare->getId());
    }

    #[Test]
    public function it_has_a_title()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertSame('title', $figshare->getTitle());
    }

    #[Test]
    public function it_has_a_width()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertSame(300, $figshare->getWidth());
    }

    #[Test]
    public function it_has_a_height()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertSame(200, $figshare->getHeight());
    }
}
