<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
#use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Collection;
#use eLife\ApiSdk\Model\PodcastEpisodeChapter;
#use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = new Builder;
    }
    
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $collection = $this->builder
            ->create(Collection::CLASS)
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
            ->create(Collection::CLASS)
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
            ->create(Collection::CLASS)
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
            ->create(Collection::CLASS)
            ->withImpactStatement('Tropical disease impact statement')
            ->__invoke();
        $withOut = $this->builder
            ->create(Collection::CLASS)
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
            ->create(Collection::CLASS)
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
            ->create(Collection::CLASS)
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
            ->create(Collection::CLASS)
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
        $collection = $this
            ->anEmptyCollection('tropical-disease', 'Tropical disease', promise_for('Tropical disease subtitle'), null, new DateTimeImmutable(), null, $image = new Image('', [900 => 'https://placehold.it/900x450']))
            ->withSubjects($subjects)
        ;

        $this->assertEquals($expected, $collection->getSubjects()->toArray());
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
                new ArraySequence([]),
                [],
            ],
            'collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    private function anEmptyCollection($id = 'tropical-disease', $title = 'Tropical disease', $subTitle = null, $impactStatement = null, $published = null, $banner = null, $thumbnail = null)
    {
        if ($subTitle === null) {
            $subTitle = promise_for('Tropical disease subtitle');
        }
        if ($published === null) {
            $published = new DateTimeImmutable();
        }
        if ($banner === null) {
            $banner = rejection_for('No banner');
        }
        if ($thumbnail === null) {
            $thumbnail = new Image('', [900 => 'https://placehold.it/900x450']);
        }
        return new Collection($id, $title, $subTitle, $impactStatement, $published, $banner, $thumbnail);
    }
}
