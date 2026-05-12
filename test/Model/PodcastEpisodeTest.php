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
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class PodcastEpisodeTest extends TestCase
{
    #[Test]
    public function it_is_a_model()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'),
            Create::promiseFor(Builder::for(Image::class)->sample('social')),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $podcastEpisode);
    }

    #[Test]
    public function it_has_an_identifier()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $podcastEpisode);
        $this->assertEquals(Identifier::podcastEpisode('1'), $podcastEpisode->getIdentifier());
    }

    #[Test]
    public function it_has_a_number()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertSame(1, $podcastEpisode->getNumber());
    }

    #[Test]
    public function it_has_a_title()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertSame('title', $podcastEpisode->getTitle());
    }

    #[Test]
    public function it_may_have_an_impact_statement()
    {
        $with = new PodcastEpisode(1, 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Subjects should not be unwrapped')),
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    #[Test]
    public function it_has_a_published_date()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, $published = new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasPublishedDate::class, $podcastEpisode);
        $this->assertEquals($published, $podcastEpisode->getPublishedDate());
    }

    #[Test]
    public function it_may_have_an_updated_date()
    {
        $with = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Subjects should not be unwrapped')),
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    #[Test]
    public function it_has_a_banner()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::promiseFor($image = Builder::for(Image::class)->sample('banner')),
            Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasBanner::class, $podcastEpisode);
        $this->assertEquals($image, $podcastEpisode->getBanner());
    }

    #[Test]
    public function it_has_a_thumbnail()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), $image = Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasThumbnail::class, $podcastEpisode);
        $this->assertEquals($image, $podcastEpisode->getThumbnail());
    }

    #[Test]
    public function it_may_have_a_social_image()
    {
        $with = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'),
            Create::promiseFor($image = Builder::for(Image::class)->sample('social')),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));
        $withOut = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'),
            Create::promiseFor(null),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertEquals($image, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    #[Test]
    public function it_has_sources()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            $sources = [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(Create::rejectionFor('Chapters should not be unwrapped')));

        $this->assertEquals($sources, $podcastEpisode->getSources());
    }

    #[Test]
    public function it_has_chapters()
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            Create::rejectionFor('No banner'), Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            $chapters = new ArraySequence([new PodcastEpisodeChapter(2, 'chapter', null, 0, null, new EmptySequence())]));

        $this->assertEquals($chapters, $podcastEpisode->getChapters());
    }
}
