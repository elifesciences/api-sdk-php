<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\RegionalCollection;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class RegionalCollectionTest extends PHPUnit_Framework_TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = Builder::for(RegionalCollection::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $regionalCollection = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $regionalCollection);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $regionalCollection = $this->builder
            ->withId('highlights-from-japan')
            ->__invoke();

        $this->assertInstanceOf(HasIdentifier::class, $regionalCollection);
        $this->assertEquals(Identifier::regionalCollection('highlights-from-japan'), $regionalCollection->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $regionalCollection = $this->builder
            ->withId('highlights-from-japan')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $regionalCollection);
        $this->assertSame('highlights-from-japan', $regionalCollection->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $regionalCollection = $this->builder
            ->withTitle('Highlights from Japan')
            ->__invoke();
        $this->assertSame('Highlights from Japan', $regionalCollection->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->builder
            ->withImpactStatement('Highlights from Japan impact statement')
            ->__invoke();
        $withOut = $this->builder
            ->withImpactStatement(null)
            ->__invoke();

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('Highlights from Japan impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $regionalCollection = $this->builder
            ->withPublishedDate($publishedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $regionalCollection);
        $this->assertEquals($publishedDate, $regionalCollection->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = $this->builder
            ->withUpdatedDate($updatedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();
        $withOut = $this->builder
            ->withUpdatedDate(null)
            ->__invoke();

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updatedDate, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $regionalCollection = $this->builder
            ->withPromiseOfBanner($image = Builder::for(Image::class)->sample('banner'))
            ->__invoke();

        $this->assertInstanceOf(HasBanner::class, $regionalCollection);
        $this->assertEquals($image, $regionalCollection->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $regionalCollection = $this->builder
            ->withThumbnail($image = Builder::for(Image::class)->sample('thumbnail'))
            ->__invoke();

        $this->assertInstanceOf(HasThumbnail::class, $regionalCollection);
        $this->assertEquals($image, $regionalCollection->getThumbnail());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $regionalCollection = $this->builder
            ->withSubjects($subjects)
            ->__invoke();

        $this->assertInstanceOf(HasSubjects::class, $regionalCollection);
        $this->assertEquals($expected, $regionalCollection->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $subjects = [
            Builder::for(Subject::class)
                ->withId('subject1')
                ->__invoke(),
            Builder::for(Subject::class)
                ->withId('subject2')
                ->__invoke(),
        ];

        return [
            'none' => [
                new EmptySequence(),
                [],
            ],
            'regional-collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_has_editors()
    {
        $regionalCollection = $this->builder
            ->withEditors($editors = new ArraySequence([Builder::dummy(Person::class)]))
            ->__invoke();

        $this->assertEquals($editors, $regionalCollection->getEditors());
    }

    /**
     * @test
     */
    public function it_has_a_summary()
    {
        $regionalCollection = $this->builder
            ->withSummary($summary = new ArraySequence([
                new Paragraph('summary'),
            ]))
            ->__invoke();

        $this->assertEquals($summary, $regionalCollection->getSummary());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $regionalCollection = $this->builder
            ->withContent($content = new ArraySequence([
                Builder::dummy(BlogArticle::class),
            ]))
            ->__invoke();

        $this->assertEquals($content, $regionalCollection->getContent());
    }

    /**
     * @test
     */
    public function it_has_related_content()
    {
        $regionalCollection = $this->builder
            ->withRelatedContent($relatedContent = new ArraySequence([
                Builder::dummy(BlogArticle::class),
            ]))
            ->__invoke();

        $this->assertEquals($relatedContent, $regionalCollection->getRelatedContent());
    }

    /**
     * @test
     */
    public function it_has_podcast_episodes()
    {
        $regionalCollection = $this->builder
            ->withPodcastEpisodes($podcastEpisodes = new ArraySequence([
                Builder::dummy(PodcastEpisode::class),
            ]))
            ->__invoke();

        $this->assertEquals($podcastEpisodes, $regionalCollection->getPodcastEpisodes());
    }
}
