<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\File;
use PHPUnit_Framework_TestCase;

final class FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_an_asset()
    {
        $file = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertInstanceOf(Asset::class, $file);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new File('10.1000/182', null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new File(null, 'id', null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new File(null, null, 'label', null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new File(null, null, null, 'title', new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new File(null, null, null, null, $caption, 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_a_media_type()
    {
        $file = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('image/jpeg', $file->getMediaType());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $file = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('http://www.example.com/image.jpg', $file->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_filename()
    {
        $file = new File(null, null, null, null, new EmptySequence(), 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('image.jpg', $file->getFilename());
    }
}
