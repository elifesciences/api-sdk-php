<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class SectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $section = new Section('title', null, new EmptySequence());

        $this->assertInstanceOf(Block::class, $section);
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $section = new Section('title', null, new EmptySequence());

        $this->assertSame('title', $section->getTitle());
    }

    public function it_may_have_an_id()
    {
        $with = new Section('title', 'id', new EmptySequence());
        $withOut = new Section('title', null, new EmptySequence());

        $this->assertInstanceOf(HasId::class, $with);
        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = new ArraySequence([new Section('sub-title', null, new EmptySequence())]);
        $section = new Section('title', null, $content);

        $this->assertInstanceOf(HasContent::class, $section);
        $this->assertEquals($content, $section->getContent());
    }
}
