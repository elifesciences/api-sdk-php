<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block\ImageFile;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ImageFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_an_asset()
    {
        $image = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertInstanceOf(Asset::class, $image);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ImageFile('10.1000/182', null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new ImageFile(null, 'id', null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new ImageFile(null, null, 'label', null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new ImageFile(null, null, null, 'title', new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new ImageFile(null, null, null, null, $caption, Builder::for(Image::class)->__invoke(), [], []);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $imageFile = new ImageFile(null, null, null, null, new EmptySequence(), $image = Builder::for(Image::class)->__invoke(), [], []);

        $this->assertEquals($image, $imageFile->getImage());
    }

    /**
     * @test
     */
    public function it_may_have_attribution()
    {
        $attribution = ['attribution'];
        $with = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), $attribution, []);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertSame($attribution, $with->getAttribution());
        $this->assertEmpty($withOut->getAttribution());
    }

    /**
     * @test
     */
    public function it_may_have_source_data()
    {
        $sourceData = [new AssetFile(null, null, null, null, new EmptySequence(), new File('text/csv', 'http://www.example.com/data.csv', 'data.csv'))];
        $with = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], $sourceData);
        $withOut = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []);

        $this->assertSame($sourceData, $with->getSourceData());
        $this->assertEmpty($withOut->getSourceData());
    }
}
