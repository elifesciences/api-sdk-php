<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class PodcastEpisodeChapterModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_episode()
    {
        $model = new PodcastEpisodeChapterModel($episode = Builder::dummy(PodcastEpisode::class), new PodcastEpisodeChapter(1, 'title', null, 0, null, new EmptySequence()));

        $this->assertSame($episode, $model->getEpisode());
    }

    /**
     * @test
     */
    public function it_has_a_chapter()
    {
        $model = new PodcastEpisodeChapterModel(Builder::dummy(PodcastEpisode::class), $chapter = new PodcastEpisodeChapter(1, 'title', null, 0, null, new EmptySequence()));

        $this->assertSame($chapter, $model->getChapter());
    }
}
