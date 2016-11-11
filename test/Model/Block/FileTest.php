<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Paragraph;
use PHPUnit_Framework_TestCase;

final class FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new File('10.1000/182', null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new File(null, 'id', null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new File(null, null, 'label', null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new File(null, null, null, 'title', [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = [new Paragraph('caption')];
        $with = new File(null, null, null, null, $caption, 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');
        $withOut = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_a_media_type()
    {
        $file = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('image/jpeg', $file->getMediaType());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $file = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('http://www.example.com/image.jpg', $file->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_filename()
    {
        $file = new File(null, null, null, null, [], 'image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg');

        $this->assertSame('image.jpg', $file->getFilename());
    }
}
