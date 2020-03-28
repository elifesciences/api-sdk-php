<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\RegionalCollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\RegionalCollections;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\RegionalCollection;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class RegionalCollectionsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var RegionalCollections */
    private $regionalCollections;

    /**
     * @before
     */
    protected function setUpRegionalCollections()
    {
        $this->regionalCollections = (new ApiSdk($this->getHttpClient()))->regionalCollections();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->regionalCollections);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockRegionalCollectionListCall(1, 1, 200);
        $this->mockRegionalCollectionListCall(1, 100, 200);
        $this->mockRegionalCollectionListCall(2, 100, 200);

        foreach ($this->regionalCollections as $i => $regionalCollection) {
            $this->assertInstanceOf(RegionalCollection::class, $regionalCollection);
            $this->assertSame((string) $i, $regionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockRegionalCollectionListCall(1, 1, 10);

        $this->assertFalse($this->regionalCollections->isEmpty());
        $this->assertSame(10, $this->regionalCollections->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockRegionalCollectionListCall(1, 1, 10);
        $this->mockRegionalCollectionListCall(1, 100, 10);

        $array = $this->regionalCollections->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $regionalCollection) {
            $this->assertInstanceOf(RegionalCollection::class, $regionalCollection);
            $this->assertSame((string) ($i + 1), $regionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockRegionalCollectionListCall(1, 1, 1);

        $this->assertTrue(isset($this->regionalCollections[0]));
        $this->assertSame('1', $this->regionalCollections[0]->getId());

        $this->mockNotFound(
            'regional-collections?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(RegionalCollectionsClient::TYPE_REGIONAL_COLLECTION_LIST, 1)]
        );

        $this->assertFalse(isset($this->regionalCollections[5]));
        $this->assertSame(null, $this->regionalCollections[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->regionalCollections[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_regional_collection()
    {
        $this->mockRegionalCollectionCall('highlights-from-japan', true);

        $regionalCollection = $this->regionalCollections->get('highlights-from-japan')->wait();

        $this->assertInstanceOf(RegionalCollection::class, $regionalCollection);
        $this->assertSame('highlights-from-japan', $regionalCollection->getId());

        $this->assertInstanceOf(BlogArticle::class, $regionalCollection->getContent()[0]);
        $this->assertSame('Media coverage: Slime can see', $regionalCollection->getContent()[0]->getTitle());

        $this->assertInstanceOf(Subject::class, $regionalCollection->getSubjects()[0]);
        $this->assertSame('Subject 1 name', $regionalCollection->getSubjects()[0]->getName());

        $this->mockSubjectCall('1');
        $this->mockSubjectCall('biophysics-structural-biology');

        $this->assertSame('Subject 1 impact statement',
            $regionalCollection->getSubjects()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5, true, ['subject']);
        $this->mockRegionalCollectionListCall(1, 100, 5, true, ['subject']);

        foreach ($this->regionalCollections->forSubject('subject') as $i => $regionalCollection) {
            $this->assertSame((string) $i, $regionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockRegionalCollectionListCall(1, 1, 10);

        $this->regionalCollections->count();

        $this->mockRegionalCollectionListCall(1, 1, 4, true, ['subject']);

        $this->assertSame(4, $this->regionalCollections->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockRegionalCollectionListCall(1, 1, 200);
        $this->mockRegionalCollectionListCall(1, 100, 200);
        $this->mockRegionalCollectionListCall(2, 100, 200);

        $this->regionalCollections->toArray();

        $this->mockRegionalCollectionListCall(1, 1, 200, true, ['subject']);
        $this->mockRegionalCollectionListCall(1, 100, 200, true, ['subject']);
        $this->mockRegionalCollectionListCall(2, 100, 200, true, ['subject']);

        $this->regionalCollections->forSubject('subject')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_contents()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5, true, [], ['article/1234', 'interview/5678']);
        $this->mockRegionalCollectionListCall(1, 100, 5, true, [], ['article/1234', 'interview/5678']);

        foreach ($this->regionalCollections->containing(Identifier::article('1234'), Identifier::interview('5678')) as $i => $regionalCollection) {
            $this->assertSame((string) $i, $regionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_contents()
    {
        $this->mockRegionalCollectionListCall(1, 1, 10);

        $this->regionalCollections->count();

        $this->mockRegionalCollectionListCall(1, 1, 4, true, [], ['article/1234', 'interview/5678']);

        $this->assertSame(4, $this->regionalCollections->containing(Identifier::article('1234'), Identifier::interview('5678'))->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_contents()
    {
        $this->mockRegionalCollectionListCall(1, 1, 200);
        $this->mockRegionalCollectionListCall(1, 100, 200);
        $this->mockRegionalCollectionListCall(2, 100, 200);

        $this->regionalCollections->toArray();

        $this->mockRegionalCollectionListCall(1, 1, 200, true, [], ['article/1234', 'interview/5678']);
        $this->mockRegionalCollectionListCall(1, 100, 200, true, [], ['article/1234', 'interview/5678']);
        $this->mockRegionalCollectionListCall(2, 100, 200, true, [], ['article/1234', 'interview/5678']);

        $this->regionalCollections->containing(Identifier::article('1234'), Identifier::interview('5678'))->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $values = $this->regionalCollections->prepend('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['foo', 'bar', '1', '2', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $values = $this->regionalCollections->append('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['1', '2', '3', '4', '5', 'foo', 'bar'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $values = $this->regionalCollections->drop(2)->map($this->tidyValue());

        $this->assertSame(['1', '2', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $values = $this->regionalCollections->insert(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $values = $this->regionalCollections->set(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '4', '5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockRegionalCollectionListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->regionalCollections->slice($offset, $length) as $i => $regionalCollection) {
            $this->assertInstanceOf(RegionalCollection::class, $regionalCollection);
            $this->assertSame($expected[$i], $regionalCollection->getId());
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
        $this->mockRegionalCollectionListCall(1, 1, 3);
        $this->mockRegionalCollectionListCall(1, 100, 3);

        $map = function (RegionalCollection $regionalCollection) {
            return $regionalCollection->getId();
        };

        $this->assertSame(
            ['1', '2', '3'],
            $this->regionalCollections->map($map)->toArray()
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $filter = function (RegionalCollection $podcastEpisode) {
            return $podcastEpisode->getId() > 3;
        };

        foreach ($this->regionalCollections->filter($filter) as $i => $podcastEpisode) {
            $this->assertSame((string) ($i + 4), $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $reduce = function (int $carry = null, RegionalCollection $podcastEpisode) {
            return $carry + $podcastEpisode->getId();
        };

        $this->assertSame(115, $this->regionalCollections->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->regionalCollections, $this->regionalCollections->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5);
        $this->mockRegionalCollectionListCall(1, 100, 5);

        $sort = function (RegionalCollection $a, RegionalCollection $b) {
            return $b->getId() <=> $a->getId();
        };

        foreach ($this->regionalCollections->sort($sort) as $i => $podcastEpisode) {
            $this->assertSame((string) (5 - $i), $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockRegionalCollectionListCall(1, 1, 5, false);
        $this->mockRegionalCollectionListCall(1, 100, 5, false);

        foreach ($this->regionalCollections->reverse() as $i => $podcastEpisode) {
            $this->assertSame((string) $i, $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockRegionalCollectionListCall(1, 1, 10);

        $this->regionalCollections->count();

        $this->assertSame(10, $this->regionalCollections->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockRegionalCollectionListCall(1, 1, 200);
        $this->mockRegionalCollectionListCall(1, 100, 200);
        $this->mockRegionalCollectionListCall(2, 100, 200);

        $this->regionalCollections->toArray();

        $this->mockRegionalCollectionListCall(1, 1, 200, false);
        $this->mockRegionalCollectionListCall(1, 100, 200, false);
        $this->mockRegionalCollectionListCall(2, 100, 200, false);

        $this->regionalCollections->reverse()->toArray();
    }
}
