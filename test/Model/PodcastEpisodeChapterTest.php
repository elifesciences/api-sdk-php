<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

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
            new ArticlePoA('id', 1, 'type', 'doi', 'author line', null, 'title',
                new DateTimeImmutable(), new DateTimeImmutable(), 1, 'elocationId', null, new ArraySequence([]), [],
                rejection_for('No abstract'), rejection_for('No issue'), rejection_for('No copyright'),
                new PromiseSequence(rejection_for('No authors'))),
        ]));

        $this->assertEquals($content, $chapter->getContent());
    }
}
