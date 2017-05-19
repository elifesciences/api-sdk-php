<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block\FigureAsset;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\HasDoi;
use PHPUnit_Framework_TestCase;

final class FigureAssetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new FigureAsset('10.7554/eLife.14107', 'label', new EmptySequence(), new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], []));
        $withOut = new FigureAsset(null, 'label', new EmptySequence(), new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], []));

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.7554/eLife.14107', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_has_a_label()
    {
        $figureAsset = new FigureAsset(null, 'label', new EmptySequence(), new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], []));

        $this->assertSame('label', $figureAsset->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_source_data()
    {
        $sourceData = new ArraySequence([new AssetFile(null, null, null, null, new EmptySequence(), new EmptySequence(), new File('text/csv', 'http://www.example.com/data.csv', 'data.csv'))]);
        $figureAsset = new FigureAsset(null, 'label', $sourceData, new Table(null, null, new EmptySequence(), new EmptySequence(), ['<table></table>'], []));

        $this->assertEquals($sourceData, $figureAsset->getSourceData());
    }
}
