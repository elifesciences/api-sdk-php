<?php

namespace test\eLife\ApiSdk\Model;

#use DateTimeImmutable;
#use eLife\ApiSdk\Collection\ArraySequence;
#use eLife\ApiSdk\Collection\PromiseSequence;
#use eLife\ApiSdk\Collection\Sequence;
#use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Collection;
#use eLife\ApiSdk\Model\PodcastEpisodeChapter;
#use eLife\ApiSdk\Model\PodcastEpisodeSource;
#use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
#use function GuzzleHttp\Promise\promise_for;
#use function GuzzleHttp\Promise\rejection_for;

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

    private function anEmptyCollection($id = 'tropical-disease', $title = 'Tropical disease')
    {
        return new Collection('tropical-disease', 'Tropical disease');
    }
}
