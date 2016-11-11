<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\File;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use PHPUnit_Framework_TestCase;

final class VideoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, [], $sources, '', 200, 100);

        $this->assertInstanceOf(Block::class, $video);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video('10.1000/182', null, null, null, [], $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, [], $sources, null, 200, 100);

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, 'id', null, null, [], $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, [], $sources, null, 200, 100);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, 'label', null, [], $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, [], $sources, null, 200, 100);

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, null, 'title', [], $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, [], $sources, null, 200, 100);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $caption = [new Paragraph('caption')];
        $with = new Video(null, null, null, null, $caption, $sources, '', 200, 100);
        $withOut = new Video(null, null, null, null, [], $sources, null, 200, 100);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_sources()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, [], $sources, '', 200, 100);

        $this->assertEquals($sources, $video->getSources());
    }

    /**
     * @test
     */
    public function it_may_have_an_image()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, null, null, [], $sources, 'http://www.example.com/image.jpeg', 200, 100);
        $withOut = new Video(null, null, null, null, [], $sources, null, 200, 100);

        $this->assertEquals('http://www.example.com/image.jpeg', $with->getImage());
        $this->assertEmpty($withOut->getImage());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, [], $sources, '', 200, 100);

        $this->assertEquals(200, $video->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, [], $sources, '', 200, 100);

        $this->assertEquals(100, $video->getHeight());
    }

    /**
     * @test
     */
    public function it_may_have_source_data()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $sourceData = [new File(null, null, null, null, [], 'text/csv', 'http://www.example.com/data.csv', 'data.csv')];
        $with = new Video(null, null, null, null, [], $sources, '', 200, 100, $sourceData);
        $withOut = new Video(null, null, null, null, [], $sources, '', 200, 100);

        $this->assertEquals($sourceData, $with->getSourceData());
        $this->assertEmpty($withOut->getSourceData());
    }
}
