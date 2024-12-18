<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSocialImage;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class PodcastEpisodeTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'),
            promise_for(Builder::for(Image::class)->sample('social')),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $podcastEpisode);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $podcastEpisode);
        $this->assertEquals(Identifier::podcastEpisode('1'), $podcastEpisode->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_a_number()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertSame(1, $podcastEpisode->getNumber());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertSame('title', $podcastEpisode->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new PodcastEpisode(1, 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
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
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasPublishedDate::class, $podcastEpisode);
        $this->assertEquals($published, $podcastEpisode->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            promise_for($image = Builder::for(Image::class)->sample('banner')),
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
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
            rejection_for('No banner'), $image = Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasThumbnail::class, $podcastEpisode);
        $this->assertEquals($image, $podcastEpisode->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $with = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'),
            promise_for($image = Builder::for(Image::class)->sample('social')),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'),
            promise_for(null),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertEquals($image, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    /**
     * @test
     */
    public function it_has_sources()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            $sources = [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        $this->assertEquals($sources, $podcastEpisode->getSources());
    }

    /**
     * @test
     */
    public function it_has_chapters()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            rejection_for('No banner'), Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            $chapters = new ArraySequence([new PodcastEpisodeChapter(2, 'chapter', null, 0, null, new EmptySequence())]));

        $this->assertEquals($chapters, $podcastEpisode->getChapters());
    }
}
