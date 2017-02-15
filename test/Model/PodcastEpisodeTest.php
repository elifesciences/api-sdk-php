<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class PodcastEpisodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $podcastEpisode);
    }

    /**
     * @test
     */
    public function it_has_a_number()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
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
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
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
        $with = new PodcastEpisode(1, 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, $published = new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($published, $podcastEpisode->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            promise_for($image = new Image('', [900 => 'https://placehold.it/900x450'])), $image,
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasBanner::class, $podcastEpisode);
        $this->assertEquals($image, $podcastEpisode->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), $image = new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasThumbnail::class, $podcastEpisode);
        $this->assertEquals($image, $podcastEpisode->getThumbnail());
    }

    /**
     * @test
     */
    public function it_has_sources()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
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
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')], $subjects,
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasSubjects::class, $podcastEpisode);
        $this->assertEquals($expected, $podcastEpisode->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $subjects = [
            new Subject('subject1', 'Subject 1', rejection_for('Subject impact statement should not be unwrapped'),
                rejection_for('No banner'), rejection_for('Subject image should not be unwrapped')),
            new Subject('subject2', 'Subject 2', rejection_for('Subject impact statement should not be unwrapped'),
                rejection_for('No banner'), rejection_for('Subject image should not be unwrapped')),
        ];

        return [
            'none' => [
                new EmptySequence(),
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
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            $chapters = new ArraySequence([new PodcastEpisodeChapter(2, 'chapter', 0, null, new EmptySequence())]));

        $this->assertEquals($chapters, $podcastEpisode->getChapters());
    }
}
