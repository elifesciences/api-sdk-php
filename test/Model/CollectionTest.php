<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
//use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Person;
//use eLife\ApiSdk\Model\PodcastEpisodeChapter;
//use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = new Builder();
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withId('tropical-disease')
            ->__invoke();
        $this->assertSame('tropical-disease', $collection->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withTitle('Tropical disease')
            ->__invoke();
        $this->assertSame('Tropical disease', $collection->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_sub_title()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withPromiseOfSubTitle('Tropical disease subtitle')
            ->__invoke();
        $this->assertSame('Tropical disease subtitle', $collection->getSubTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->builder
            ->create(Collection::class)
            ->withImpactStatement('Tropical disease impact statement')
            ->__invoke();
        $withOut = $this->builder
            ->create(Collection::class)
            ->withImpactStatement(null)
            ->__invoke();

        $this->assertSame('Tropical disease impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withPublishedDate($publishedDate = new DateTimeImmutable())
            ->__invoke();

        $this->assertEquals($publishedDate, $collection->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withPromiseOfBanner(
                $image = new Image('', [900 => 'https://placehold.it/900x450'])
            )
            ->__invoke();

        $this->assertEquals($image, $collection->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withThumbnail(
                $image = new Image('', [70 => 'https://placehold.it/70x140'])
            )
            ->__invoke();

        $this->assertEquals($image, $collection->getThumbnail());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withSubjects($subjects)
            ->__invoke()
        ;

        $this->assertEquals($expected, $collection->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $this->builder = new Builder();
        $subjects = [
            $this->builder
                ->create(Subject::class)
                ->withId('subject1')
                ->__invoke(),
            $this->builder
                ->create(Subject::class)
                ->withId('subject2')
                ->__invoke(),
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
    public function it_has_a_selected_curator()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withSelectedCurator($person = Builder::dummy(Person::class))
            ->withSelectedCuratorEtAl(true)
            ->__invoke()
        ;

        $this->assertEquals($person, $collection->getSelectedCurator());
        $this->assertTrue($collection->selectedCuratorEtAl());
    }

    /**
     * @test
     */
    public function it_has_curators()
    {
        $collection = $this->builder
            ->create(Collection::class)
            ->withCurators($curators = new ArraySequence([Builder::dummy(Person::class)]))
            ->__invoke()
        ;

        $this->assertEquals($curators, $collection->getCurators());
    }
}
