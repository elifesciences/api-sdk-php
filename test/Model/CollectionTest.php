<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class CollectionTest extends PHPUnit_Framework_TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = Builder::for(Collection::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $collection = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $collection);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $collection = $this->builder
            ->withId('tropical-disease')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $collection);
        $this->assertSame('tropical-disease', $collection->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $collection = $this->builder
            ->withTitle('Tropical disease')
            ->__invoke();
        $this->assertSame('Tropical disease', $collection->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_sub_title()
    {
        $with = $this->builder
            ->withTitle('Tropical disease')
            ->withPromiseOfSubTitle('Tropical disease subtitle')
            ->__invoke();
        $without = $this->builder
            ->withTitle('Tropical disease')
            ->withPromiseOfSubTitle(null)
            ->__invoke();

        $this->assertSame('Tropical disease subtitle', $with->getSubTitle());
        $this->assertSame('Tropical disease: Tropical disease subtitle', $with->getFullTitle());
        $this->assertNull($without->getSubTitle());
        $this->assertSame('Tropical disease', $without->getFullTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->builder
            ->withImpactStatement('Tropical disease impact statement')
            ->__invoke();
        $withOut = $this->builder
            ->withImpactStatement(null)
            ->__invoke();

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('Tropical disease impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $collection = $this->builder
            ->withPublishedDate($publishedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $collection);
        $this->assertEquals($publishedDate, $collection->getPublishedDate());
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
        $collection = $this->builder
            ->withPromiseOfBanner($image = Builder::for(Image::class)->sample('banner'))
            ->__invoke();

        $this->assertInstanceOf(HasBanner::class, $collection);
        $this->assertEquals($image, $collection->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $collection = $this->builder
            ->withThumbnail($image = Builder::for(Image::class)->sample('thumbnail'))
            ->__invoke();

        $this->assertInstanceOf(HasThumbnail::class, $collection);
        $this->assertEquals($image, $collection->getThumbnail());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $collection = $this->builder
            ->withSubjects($subjects)
            ->__invoke();

        $this->assertInstanceOf(HasSubjects::class, $collection);
        $this->assertEquals($expected, $collection->getSubjects()->toArray());
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
            'collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_has_a_selected_curator()
    {
        $collection = $this->builder
            ->withSelectedCurator($person = Builder::dummy(Person::class))
            ->withSelectedCuratorEtAl(true)
            ->__invoke();

        $this->assertEquals($person, $collection->getSelectedCurator());
        $this->assertTrue($collection->selectedCuratorEtAl());
    }

    /**
     * @test
     */
    public function it_has_curators()
    {
        $collection = $this->builder
            ->withCurators($curators = new ArraySequence([Builder::dummy(Person::class)]))
            ->__invoke();

        $this->assertEquals($curators, $collection->getCurators());
    }

    /**
     * @test
     */
    public function it_has_a_summary()
    {
        $collection = $this->builder
            ->withSummary($summary = new ArraySequence([
                new Paragraph('summary'),
            ]))
            ->__invoke();

        $this->assertEquals($summary, $collection->getSummary());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $collection = $this->builder
            ->withContent($content = new ArraySequence([
                Builder::dummy(BlogArticle::class),
            ]))
            ->__invoke();

        $this->assertEquals($content, $collection->getContent());
    }

    /**
     * @test
     */
    public function it_has_related_content()
    {
        $collection = $this->builder
            ->withRelatedContent($relatedContent = new ArraySequence([
                Builder::dummy(BlogArticle::class),
            ]))
            ->__invoke();

        $this->assertEquals($relatedContent, $collection->getRelatedContent());
    }

    /**
     * @test
     */
    public function it_has_podcast_episodes()
    {
        $collection = $this->builder
            ->withPodcastEpisodes($podcastEpisodes = new ArraySequence([
                Builder::dummy(PodcastEpisode::class),
            ]))
            ->__invoke();

        $this->assertEquals($podcastEpisodes, $collection->getPodcastEpisodes());
    }
}
