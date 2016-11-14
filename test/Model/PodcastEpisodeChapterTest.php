<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class PodcastEpisodeChapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_number()
    {
        $chapter = new PodcastEpisodeChapter(1, 'chapter', 0, null, new ArraySequence([]));

        $this->assertSame(1, $chapter->getNumber());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $chapter = new PodcastEpisodeChapter(1, 'chapter', 0, null, new ArraySequence([]));

        $this->assertSame('chapter', $chapter->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_time()
    {
        $chapter = new PodcastEpisodeChapter(1, 'chapter', 0, null, new ArraySequence([]));

        $this->assertSame(0, $chapter->getTime());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new PodcastEpisodeChapter(1, 'chapter', 0, 'impact statement', new ArraySequence([]));
        $withOut = new PodcastEpisodeChapter(1, 'chapter', 0, null, new ArraySequence([]));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $chapter = new PodcastEpisodeChapter(1, 'chapter', 0, null, $content = new ArraySequence([
            Builder::dummy(ArticlePoA::class),
        ]));

        $this->assertEquals($content, $chapter->getContent());
    }
}
