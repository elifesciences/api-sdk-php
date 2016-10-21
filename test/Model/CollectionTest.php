<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
#use eLife\ApiSdk\Collection\ArraySequence;
#use eLife\ApiSdk\Collection\PromiseSequence;
#use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Collection;
#use eLife\ApiSdk\Model\PodcastEpisodeChapter;
#use eLife\ApiSdk\Model\PodcastEpisodeSource;
#use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $collection = $this->anEmptyCollection('tropical-disease', 'Tropical disease');
        $this->assertSame('tropical-disease', $collection->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $collection = $this->anEmptyCollection('tropical-disease', 'Tropical disease');
        $this->assertSame('Tropical disease', $collection->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->anEmptyCollection('tropical-disease', 'Tropical disease', 'impact statement');
        $withOut = $this->anEmptyCollection('tropical-disease', 'Tropical disease', null);

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $collection = $this->anEmptyCollection('tropical-disease', 'Tropical disease', null, $published = new DateTimeImmutable());

        $this->assertEquals($published, $collection->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $collection = $this->anEmptyCollection('tropical-disease', 'Tropical disease', null, new DateTimeImmutable(), promise_for($image = new Image('', [900 => 'https://placehold.it/900x450'])));

        $this->assertEquals($image, $collection->getBanner());
    }


    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $collection = $this->anEmptyCollection('tropical-disease', 'Tropical disease', null, new DateTimeImmutable(), null, $image = new Image('', [900 => 'https://placehold.it/900x450']));

        $this->assertEquals($image, $collection->getThumbnail());
    }

    private function anEmptyCollection($id = 'tropical-disease', $title = 'Tropical disease', $impactStatement = null, $published = null, $banner = null, $thumbnail = null)
    {
        if ($published === null) {
            $published = new DateTimeImmutable();
        }
        if ($banner === null) {
            $banner = rejection_for('No banner');
        }
        if ($thumbnail === null) {
            $thumbnail = new Image('', [900 => 'https://placehold.it/900x450']);
        }
        return new Collection($id, $title, $impactStatement, $published, $banner, $thumbnail);
    }
}
