<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\YouTube;
use PHPUnit_Framework_TestCase;

final class YouTubeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $video = new YouTube('foo', 300, 200);

        $this->assertInstanceOf(Block::class, $video);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $video = new YouTube('foo', 300, 200);

        $this->assertSame('foo', $video->getId());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $video = new YouTube('foo', 300, 200);

        $this->assertSame(300, $video->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $video = new YouTube('foo', 300, 200);

        $this->assertSame(200, $video->getHeight());
    }
}
