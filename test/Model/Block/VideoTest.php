<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\AssetBlock;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class VideoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertInstanceOf(AssetBlock::class, $video);
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video('id', null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, 'title', new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

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
        $with = new Video(null, null, $caption, new EmptySequence(), $sources, null, 200, 100);
        $withOut = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_may_have_attribution()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $attribution = new ArraySequence(['attribution']);
        $with = new Video(null, null, new EmptySequence(), $attribution, $sources, null, 200, 100);
        $withOut = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals($attribution, $with->getAttribution());
        $this->assertEmpty($withOut->getAttribution());
    }

    /**
     * @test
     */
    public function it_has_sources()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals($sources, $video->getSources());
    }

    /**
     * @test
     */
    public function it_may_have_a_placeholder()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $with = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, $placeholder = Builder::dummy(Image::class), 200, 100);
        $withOut = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals($placeholder, $with->getPlaceholder());
        $this->assertEmpty($withOut->getPlaceholder());
    }

    /**
     * @test
     */
    public function it_has_a_width()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals(200, $video->getWidth());
    }

    /**
     * @test
     */
    public function it_has_a_height()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        $this->assertEquals(100, $video->getHeight());
    }

    /**
     * @test
     */
    public function it_may_be_autoplayed()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100, true);

        $this->assertTrue($video->isAutoplay());
    }

    /**
     * @test
     */
    public function it_may_be_looped()
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100, false, true);

        $this->assertTrue($video->isLoop());
    }
}
