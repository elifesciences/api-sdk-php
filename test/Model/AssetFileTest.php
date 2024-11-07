<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\HasAttribution;
use eLife\ApiSdk\Model\HasCaption;
use PHPUnit\Framework\TestCase;

final class AssetFileTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_asset()
    {
        $file = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertInstanceOf(HasCaption::class, $file);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new AssetFile('10.1000/182', 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $file = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('id', $file->getId());
    }

    /**
     * @test
     */
    public function it_has_a_label()
    {
        $file = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('label', $file->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new AssetFile(null, 'id', 'label', 'title', new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new AssetFile(null, 'id', 'label', null, $caption, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_may_have_attribution()
    {
        $attribution = new ArraySequence(['attribution']);
        $with = new AssetFile(null, 'id', 'label', null, new EmptySequence(), $attribution, new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertInstanceOf(HasAttribution::class, $with);
        $this->assertEquals($attribution, $with->getAttribution());
        $this->assertEmpty($withOut->getAttribution());
    }

    /**
     * @test
     */
    public function it_has_a_file()
    {
        $file = new AssetFile(null, 'id', 'label', null, new EmptySequence(), new EmptySequence(), $theFile = new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertEquals($theFile, $file->getFile());
    }
}
