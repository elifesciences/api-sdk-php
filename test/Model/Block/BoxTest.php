<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use PHPUnit\Framework\TestCase;

final class BoxTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $box = new Box(null, null, null, 'title', new EmptySequence());

        $this->assertInstanceOf(Block::class, $box);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new Box('10.1000/182', null, null, 'title', new EmptySequence());
        $withOut = new Box(null, null, null, 'title', new EmptySequence());

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Box(null, 'id', null, 'title', new EmptySequence());
        $withOut = new Box(null, null, null, 'title', new EmptySequence());

        $this->assertInstanceOf(HasId::class, $with);
        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new Box(null, null, 'label', 'title', new EmptySequence());
        $withOut = new Box(null, null, null, 'title', new EmptySequence());

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $box = new Box(null, null, null, 'title', new EmptySequence());

        $this->assertSame('title', $box->getTitle());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = new ArraySequence([new Box(null, null, null, 'sub-title', new EmptySequence())]);
        $box = new Box(null, null, null, 'title', $content);

        $this->assertInstanceOf(HasContent::class, $box);
        $this->assertEquals($content, $box->getContent());
    }
}
