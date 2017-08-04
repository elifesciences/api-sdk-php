<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\Image as ImageFile;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $image = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke());

        $this->assertInstanceOf(BlockWithCaption::class, $image);
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new Image('id', null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke());
        $withOut = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke());

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new Image(null, 'title', new EmptySequence(), Builder::for(ImageFile::class)->__invoke());
        $withOut = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke());

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new Image(null, null, $caption, Builder::for(ImageFile::class)->__invoke());
        $withOut = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke());

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_may_have_attribution()
    {
        $attribution = new ArraySequence(['attribution']);
        $with = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->withAttribution($attribution)->__invoke());
        $withOut = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->withAttribution(new EmptySequence())->__invoke());

        $this->assertSame($attribution, $with->getAttribution());
        $this->assertEmpty($withOut->getAttribution());
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $imageFile = new Image(null, null, new EmptySequence(), $image = Builder::for(ImageFile::class)->__invoke());

        $this->assertEquals($image, $imageFile->getImage());
    }

    /**
     * @test
     */
    public function it_may_be_inlined()
    {
        $true = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke(), true);
        $false = new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke());

        $this->assertTrue($true->isInline());
        $this->assertFalse($false->isInline());
    }
}
