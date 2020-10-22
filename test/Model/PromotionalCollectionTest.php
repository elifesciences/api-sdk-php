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
use eLife\ApiSdk\Model\HasSocialImage;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class PromotionalCollectionTest extends PHPUnit_Framework_TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = Builder::for(PromotionalCollection::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $promotionalCollection = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $promotionalCollection);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $promotionalCollection = $this->builder
            ->withId('highlights-from-japan')
            ->__invoke();

        $this->assertInstanceOf(HasIdentifier::class, $promotionalCollection);
        $this->assertEquals(Identifier::promotionalCollection('highlights-from-japan'), $promotionalCollection->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $promotionalCollection = $this->builder
            ->withId('highlights-from-japan')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $promotionalCollection);
        $this->assertSame('highlights-from-japan', $promotionalCollection->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $promotionalCollection = $this->builder
            ->withTitle('Highlights from Japan')
            ->__invoke();
        $this->assertSame('Highlights from Japan', $promotionalCollection->getTitle());
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
        $promotionalCollection = $this->builder
            ->withPublishedDate($publishedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $promotionalCollection);
        $this->assertEquals($publishedDate, $promotionalCollection->getPublishedDate());
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
        $promotionalCollection = $this->builder
            ->withPromiseOfBanner($image = Builder::for(Image::class)->sample('banner'))
            ->__invoke();

        $this->assertInstanceOf(HasBanner::class, $promotionalCollection);
        $this->assertEquals($image, $promotionalCollection->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $promotionalCollection = $this->builder
            ->withThumbnail($image = Builder::for(Image::class)->sample('thumbnail'))
            ->__invoke();

        $this->assertInstanceOf(HasThumbnail::class, $promotionalCollection);
        $this->assertEquals($image, $promotionalCollection->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $with = $this->builder
            ->withPromiseOfSocialImage($image = Builder::for(Image::class)->sample('social'))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfSocialImage(null)
            ->__invoke();

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertSame($image, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $promotionalCollection = $this->builder
            ->withSubjects($subjects)
            ->__invoke();

        $this->assertInstanceOf(HasSubjects::class, $promotionalCollection);
        $this->assertEquals($expected, $promotionalCollection->getSubjects()->toArray());
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
            'promotional-collection' => [
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
        $promotionalCollection = $this->builder
            ->withEditors($editors = new ArraySequence([Builder::dummy(Person::class)]))
            ->__invoke();

        $this->assertEquals($editors, $promotionalCollection->getEditors());
    }

    /**
     * @test
     */
    public function it_has_a_summary()
    {
        $promotionalCollection = $this->builder
            ->withSummary($summary = new ArraySequence([
                new Paragraph('summary'),
            ]))
            ->__invoke();

        $this->assertEquals($summary, $promotionalCollection->getSummary());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $promotionalCollection = $this->builder
            ->withContent($content = new ArraySequence([
                Builder::dummy(BlogArticle::class),
            ]))
            ->__invoke();

        $this->assertEquals($content, $promotionalCollection->getContent());
    }

    /**
     * @test
     */
    public function it_has_related_content()
    {
        $promotionalCollection = $this->builder
            ->withRelatedContent($relatedContent = new ArraySequence([
                Builder::dummy(BlogArticle::class),
            ]))
            ->__invoke();

        $this->assertEquals($relatedContent, $promotionalCollection->getRelatedContent());
    }

    /**
     * @test
     */
    public function it_has_podcast_episodes()
    {
        $promotionalCollection = $this->builder
            ->withPodcastEpisodes($podcastEpisodes = new ArraySequence([
                Builder::dummy(PodcastEpisode::class),
            ]))
            ->__invoke();

        $this->assertEquals($podcastEpisodes, $promotionalCollection->getPodcastEpisodes());
    }
}
