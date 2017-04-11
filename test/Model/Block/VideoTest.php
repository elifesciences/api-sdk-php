<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Model\File;
use PHPUnit_Framework_TestCase;

final class VideoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100);

        $this->assertInstanceOf(Block::class, $video);
        $this->assertInstanceOf(Asset::class, $video);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video('10.1000/182', null, null, null, new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, null, 200, 100);

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, 'id', null, null, new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, null, 200, 100);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, 'label', null, new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, null, 200, 100);

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, null, 'title', new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, null, 200, 100);

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new Video(null, null, null, null, $caption, $sources, '', 200, 100);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_sources()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100);

        $this->assertEquals($sources, $video->getSources());
    }

    /**
     * @test
     */
    public function it_may_have_an_image()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, null, null, new EmptySequence(), $sources, 'http://www.example.com/image.jpeg', 200, 100);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals('http://www.example.com/image.jpeg', $with->getImage());
        $this->assertEmpty($withOut->getImage());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100);

        $this->assertEquals(200, $video->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100);

        $this->assertEquals(100, $video->getHeight());
    }

    /**
     * @test
     */
    public function it_may_be_autoplayed()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100, true);

        $this->assertTrue($video->isAutoplay());
    }

    /**
     * @test
     */
    public function it_may_be_looped()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100, false, true);

        $this->assertTrue($video->isLoop());
    }

    /**
     * @test
     */
    public function it_may_have_source_data()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $sourceData = [new AssetFile(null, null, null, null, new EmptySequence(), new File('text/csv', 'http://www.example.com/data.csv', 'data.csv'))];
        $with = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100, false, false, $sourceData);
        $withOut = new Video(null, null, null, null, new EmptySequence(), $sources, '', 200, 100);

        $this->assertEquals($sourceData, $with->getSourceData());
        $this->assertEmpty($withOut->getSourceData());
    }
}
