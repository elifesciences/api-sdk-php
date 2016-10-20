<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class PodcastEpisodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_number()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertSame(1, $podcastEpisode->getNumber());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertSame('title', $podcastEpisode->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new PodcastEpisode(1, 'title', 'impact statement', new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, $published = new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($published, $podcastEpisode->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            $image = new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($image, $podcastEpisode->getImage());
    }

    /**
     * @test
     */
    public function it_has_sources()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            $sources = [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($sources, $podcastEpisode->getSources());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')], $subjects,
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($expected, $podcastEpisode->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $subjects = [
            new Subject('subject1', 'Subject 1', rejection_for('Subject impact statement should not be unwrapped'),
                rejection_for('Subject image should not be unwrapped')),
            new Subject('subject2', 'Subject 2', rejection_for('Subject impact statement should not be unwrapped'),
                rejection_for('Subject image should not be unwrapped')),
        ];

        return [
            'none' => [
                new ArraySequence([]),
                [],
            ],
            'collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_has_chapters()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable(),
            new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            $chapters = new ArraySequence([new PodcastEpisodeChapter(2, 'chapter', 0, null, new ArraySequence([]))]));

        $this->assertEquals($chapters, $podcastEpisode->getChapters());
    }
}
