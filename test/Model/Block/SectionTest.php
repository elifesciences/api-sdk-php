<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Section;
use PHPUnit_Framework_TestCase;

final class SectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $section = new Section('title', null, []);

        $this->assertInstanceOf(Block::class, $section);
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $section = new Section('title', null, []);

        $this->assertSame('title', $section->getTitle());
    }

    public function it_may_have_an_id()
    {
        $with = new Section('title', 'id', []);
        $withOut = new Section('title', null, []);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Section('sub-title', null, [])];
        $section = new Section('title', null, $content);

        $this->assertEquals($content, $section->getContent());
    }
}
