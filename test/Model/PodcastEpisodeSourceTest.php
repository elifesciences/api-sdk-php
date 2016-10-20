<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\PodcastEpisodeSource;
use PHPUnit_Framework_TestCase;

final class PodcastEpisodeSourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_media_type()
    {
        $source = new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3');

        $this->assertSame('audio/mpeg', $source->getMediaType());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $source = new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3');

        $this->assertSame('https://www.example.com/episode.mp3', $source->getUri());
    }
}
