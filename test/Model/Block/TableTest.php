<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\Footnote;
use eLife\ApiSdk\Model\HasAttribution;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $table = new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], [], []);

        $this->assertInstanceOf(BlockWithCaption::class, $table);
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Table('id', null, new EmptySequence(), new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, new EmptySequence(), new EmptySequence(), [], [], []);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new Table(null, 'title', new EmptySequence(), new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, new EmptySequence(), new EmptySequence(), [], [], []);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $with = new Table(null, null, $caption = new ArraySequence([new Paragraph('foo')]), new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, new EmptySequence(), new EmptySequence(), [], [], []);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_may_have_attribution()
    {
        $attribution = new ArraySequence(['attribution']);
        $with = new Table(null, null, new EmptySequence(), $attribution, [], [], []);
        $withOut = new Table(null, null, new EmptySequence(), new EmptySequence(), [], [], []);

        $this->assertInstanceOf(HasAttribution::class, $with);
        $this->assertEquals($attribution, $with->getAttribution());
        $this->assertEmpty($withOut->getAttribution());
    }

    /**
     * @test
     */
    public function it_has_tables()
    {
        $table = new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], [], []);

        $this->assertSame(['<table></table>'], $table->getTables());
    }

    /**
     * @test
     */
    public function it_may_have_footnotes()
    {
        $with = new Table(null, null, new EmptySequence(), new EmptySequence(), [], $footnotes = [new Footnote(null, null, new ArraySequence([new Paragraph('foo')]))], []);
        $withOut = new Table(null, null, new EmptySequence(), new EmptySequence(), [], [], []);

        $this->assertEquals($footnotes, $with->getFootnotes());
        $this->assertEmpty($withOut->getFootnotes());
    }
}
