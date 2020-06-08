<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figshare;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class FigshareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertInstanceOf(Block::class, $figshare);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertInstanceOf(HasId::class, $figshare);
        $this->assertSame('foo', $figshare->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertSame('title', $figshare->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertSame(300, $figshare->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $figshare = new Figshare('foo', 'title', 300, 200);

        $this->assertSame(200, $figshare->getHeight());
    }
}
