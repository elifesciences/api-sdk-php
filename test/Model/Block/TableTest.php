<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Model\File;
use PHPUnit_Framework_TestCase;

final class TableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $table = new Table(null, null, null, null, new EmptySequence(), ['<table></table>'], [], []);

        $this->assertInstanceOf(Block::class, $table);
        $this->assertInstanceOf(Asset::class, $table);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new Table('10.1000/182', null, null, null, new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Table(null, 'id', null, null, new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new Table(null, null, 'label', null, new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new Table(null, null, null, 'title', new EmptySequence(), [], [], []);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $with = new Table(null, null, null, null, $caption = new ArraySequence([new Paragraph('foo')]), [], [], []);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_tables()
    {
        $table = new Table(null, null, null, null, new EmptySequence(), ['<table></table>'], [], []);

        $this->assertSame(['<table></table>'], $table->getTables());
    }

    /**
     * @test
     */
    public function it_may_have_a_footer()
    {
        $with = new Table(null, null, null, null, new EmptySequence(), [], $footer = [new Paragraph('foo')], []);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertEquals($footer, $with->getFooter());
        $this->assertEmpty($withOut->getFooter());
    }

    /**
     * @test
     */
    public function it_may_have_source_data()
    {
        $sourceData = [new File(null, null, null, null, new EmptySequence(), 'text/csv', 'http://www.example.com/data.csv', 'data.csv')];
        $with = new Table(null, null, null, null, new EmptySequence(), [], [], $sourceData);
        $withOut = new Table(null, null, null, null, new EmptySequence(), [], [], []);

        $this->assertEquals($sourceData, $with->getSourceData());
        $this->assertEmpty($withOut->getSourceData());
    }
}
