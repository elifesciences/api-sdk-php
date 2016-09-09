<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Table;
use PHPUnit_Framework_TestCase;

final class TableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new Table('10.1000/182', null, null, null, [], [], [], []);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Table(null, 'id', null, null, [], [], [], []);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new Table(null, null, 'label', null, [], [], [], []);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new Table(null, null, null, 'title', [], [], [], []);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $with = new Table(null, null, null, null, $caption = [new Paragraph('foo')], [], [], []);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_tables()
    {
        $table = new Table(null, null, null, null, [], ['<table></table>'], [], []);

        $this->assertSame(['<table></table>'], $table->getTables());
    }

    /**
     * @test
     */
    public function it_may_have_a_footer()
    {
        $with = new Table(null, null, null, null, [], [], $footer = [new Paragraph('foo')], []);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertEquals($footer, $with->getFooter());
        $this->assertEmpty($withOut->getFooter());
    }

    /**
     * @test
     */
    public function it_may_have_source_data()
    {
        $sourceData = [new File(null, null, null, null, [], 'text/csv', 'http://www.example.com/data.csv')];
        $with = new Table(null, null, null, null, [], [], [], $sourceData);
        $withOut = new Table(null, null, null, null, [], [], [], []);

        $this->assertEquals($sourceData, $with->getSourceData());
        $this->assertEmpty($withOut->getSourceData());
    }
}
