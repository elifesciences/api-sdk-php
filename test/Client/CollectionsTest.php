<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class CollectionsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Collections */
    private $collections;

    /**
     * @before
     */
    protected function setUpCollections()
    {
        $this->collections = (new ApiSdk($this->getHttpClient()))->collections();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->collections);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockCollectionListCall(1, 1, 200);
        $this->mockCollectionListCall(1, 100, 200);
        $this->mockCollectionListCall(2, 100, 200);

        foreach ($this->collections as $i => $collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertSame((string) $i, $collection->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockCollectionListCall(1, 1, 10);

        $this->assertFalse($this->collections->isEmpty());
        $this->assertSame(10, $this->collections->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockCollectionListCall(1, 1, 10);
        $this->mockCollectionListCall(1, 100, 10);

        $array = $this->collections->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertSame((string) ($i + 1), $collection->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockCollectionListCall(1, 1, 1);

        $this->assertTrue(isset($this->collections[0]));
        $this->assertSame('1', $this->collections[0]->getId());

        $this->mockNotFound(
            'collections?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)]
        );

        $this->assertFalse(isset($this->collections[5]));
        $this->assertSame(null, $this->collections[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->collections[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_collection()
    {
        $this->mockCollectionCall('tropical-disease', true);

        $collection = $this->collections->get('tropical-disease')->wait();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame('tropical-disease', $collection->getId());

        $this->assertInstanceOf(BlogArticle::class, $collection->getContent()[0]);
        $this->assertSame('Media coverage: Slime can see', $collection->getContent()[0]->getTitle());

        $this->assertInstanceOf(Subject::class, $collection->getSubjects()[0]);
        $this->assertSame('Subject 1 name', $collection->getSubjects()[0]->getName());

        $this->mockSubjectCall('1');
        $this->mockSubjectCall('biophysics-structural-biology');

        $this->assertSame('Subject 1 impact statement',
            $collection->getSubjects()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockCollectionListCall(1, 1, 5, true, ['subject']);
        $this->mockCollectionListCall(1, 100, 5, true, ['subject']);

        foreach ($this->collections->forSubject('subject') as $i => $collection) {
            $this->assertSame((string) $i, $collection->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockCollectionListCall(1, 1, 10);

        $this->collections->count();

        $this->mockCollectionListCall(1, 1, 4, true, ['subject']);

        $this->assertSame(4, $this->collections->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockCollectionListCall(1, 1, 200);
        $this->mockCollectionListCall(1, 100, 200);
        $this->mockCollectionListCall(2, 100, 200);

        $this->collections->toArray();

        $this->mockCollectionListCall(1, 1, 200, true, ['subject']);
        $this->mockCollectionListCall(1, 100, 200, true, ['subject']);
        $this->mockCollectionListCall(2, 100, 200, true, ['subject']);

        $this->collections->forSubject('subject')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $values = $this->collections->prepend('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['foo', 'bar', '1', '2', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $values = $this->collections->append('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['1', '2', '3', '4', '5', 'foo', 'bar'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $values = $this->collections->drop(2)->map($this->tidyValue());

        $this->assertSame(['1', '2', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $values = $this->collections->insert(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $values = $this->collections->set(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '4', '5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockCollectionListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->collections->slice($offset, $length) as $i => $collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertSame($expected[$i], $collection->getId());
        }
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [
                1,
                1,
                ['2'],
                [
                    ['page' => 2, 'per-page' => 1],
                ],
            ],
            'offset -2, no length' => [
                -2,
                null,
                ['4', '5'],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
            'offset 6, no length' => [
                6,
                null,
                [],
                [
                    ['page' => 1, 'per-page' => 1],
                    ['page' => 1, 'per-page' => 100],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockCollectionListCall(1, 1, 3);
        $this->mockCollectionListCall(1, 100, 3);

        $map = function (Collection $collection) {
            return $collection->getId();
        };

        $this->assertSame(
            ['1', '2', '3'],
            $this->collections->map($map)->toArray()
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $filter = function (Collection $podcastEpisode) {
            return $podcastEpisode->getId() > 3;
        };

        foreach ($this->collections->filter($filter) as $i => $podcastEpisode) {
            $this->assertSame((string) ($i + 4), $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $reduce = function (int $carry = null, Collection $podcastEpisode) {
            return $carry + $podcastEpisode->getId();
        };

        $this->assertSame(115, $this->collections->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockCollectionListCall(1, 1, 5);
        $this->mockCollectionListCall(1, 100, 5);

        $sort = function (Collection $a, Collection $b) {
            return $b->getId() <=> $a->getId();
        };

        foreach ($this->collections->sort($sort) as $i => $podcastEpisode) {
            $this->assertSame((string) (5 - $i), $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockCollectionListCall(1, 1, 5, false);
        $this->mockCollectionListCall(1, 100, 5, false);

        foreach ($this->collections->reverse() as $i => $podcastEpisode) {
            $this->assertSame((string) $i, $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockCollectionListCall(1, 1, 10);

        $this->collections->count();

        $this->assertSame(10, $this->collections->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockCollectionListCall(1, 1, 200);
        $this->mockCollectionListCall(1, 100, 200);
        $this->mockCollectionListCall(2, 100, 200);

        $this->collections->toArray();

        $this->mockCollectionListCall(1, 1, 200, false);
        $this->mockCollectionListCall(1, 100, 200, false);
        $this->mockCollectionListCall(2, 100, 200, false);

        $this->collections->reverse()->toArray();
    }
}
