<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\PromotionalCollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PromotionalCollections;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class PromotionalCollectionsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var PromotionalCollections */
    private $promotionalCollections;

    /**
     * @before
     */
    protected function setUpPromotionalCollections()
    {
        $this->promotionalCollections = (new ApiSdk($this->getHttpClient()))->promotionalCollections();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->promotionalCollections);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 200);
        $this->mockPromotionalCollectionListCall(1, 100, 200);
        $this->mockPromotionalCollectionListCall(2, 100, 200);

        foreach ($this->promotionalCollections as $i => $promotionalCollection) {
            $this->assertInstanceOf(PromotionalCollection::class, $promotionalCollection);
            $this->assertSame((string) $i, $promotionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 10);

        $this->assertFalse($this->promotionalCollections->isEmpty());
        $this->assertSame(10, $this->promotionalCollections->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 10);
        $this->mockPromotionalCollectionListCall(1, 100, 10);

        $array = $this->promotionalCollections->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $promotionalCollection) {
            $this->assertInstanceOf(PromotionalCollection::class, $promotionalCollection);
            $this->assertSame((string) ($i + 1), $promotionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 1);

        $this->assertTrue(isset($this->promotionalCollections[0]));
        $this->assertSame('1', $this->promotionalCollections[0]->getId());

        $this->mockNotFound(
            'promotional-collections?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION_LIST, 1)]
        );

        $this->assertFalse(isset($this->promotionalCollections[5]));
        $this->assertSame(null, $this->promotionalCollections[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->promotionalCollections[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_promotional_collection()
    {
        $this->mockPromotionalCollectionCall('highlights-from-japan', true);

        $promotionalCollection = $this->promotionalCollections->get('highlights-from-japan')->wait();

        $this->assertInstanceOf(PromotionalCollection::class, $promotionalCollection);
        $this->assertSame('highlights-from-japan', $promotionalCollection->getId());

        $this->assertInstanceOf(BlogArticle::class, $promotionalCollection->getContent()[0]);
        $this->assertSame('Media coverage: Slime can see', $promotionalCollection->getContent()[0]->getTitle());

        $this->assertInstanceOf(Subject::class, $promotionalCollection->getSubjects()[0]);
        $this->assertSame('Subject 1 name', $promotionalCollection->getSubjects()[0]->getName());

        $this->mockSubjectCall('1');
        $this->mockSubjectCall('biophysics-structural-biology');

        $this->assertSame('Subject 1 impact statement',
            $promotionalCollection->getSubjects()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5, true, ['subject']);
        $this->mockPromotionalCollectionListCall(1, 100, 5, true, ['subject']);

        foreach ($this->promotionalCollections->forSubject('subject') as $i => $promotionalCollection) {
            $this->assertSame((string) $i, $promotionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 10);

        $this->promotionalCollections->count();

        $this->mockPromotionalCollectionListCall(1, 1, 4, true, ['subject']);

        $this->assertSame(4, $this->promotionalCollections->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 200);
        $this->mockPromotionalCollectionListCall(1, 100, 200);
        $this->mockPromotionalCollectionListCall(2, 100, 200);

        $this->promotionalCollections->toArray();

        $this->mockPromotionalCollectionListCall(1, 1, 200, true, ['subject']);
        $this->mockPromotionalCollectionListCall(1, 100, 200, true, ['subject']);
        $this->mockPromotionalCollectionListCall(2, 100, 200, true, ['subject']);

        $this->promotionalCollections->forSubject('subject')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_contents()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5, true, [], ['article/1234', 'interview/5678']);
        $this->mockPromotionalCollectionListCall(1, 100, 5, true, [], ['article/1234', 'interview/5678']);

        foreach ($this->promotionalCollections->containing(Identifier::article('1234'), Identifier::interview('5678')) as $i => $promotionalCollection) {
            $this->assertSame((string) $i, $promotionalCollection->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_contents()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 10);

        $this->promotionalCollections->count();

        $this->mockPromotionalCollectionListCall(1, 1, 4, true, [], ['article/1234', 'interview/5678']);

        $this->assertSame(4, $this->promotionalCollections->containing(Identifier::article('1234'), Identifier::interview('5678'))->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_contents()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 200);
        $this->mockPromotionalCollectionListCall(1, 100, 200);
        $this->mockPromotionalCollectionListCall(2, 100, 200);

        $this->promotionalCollections->toArray();

        $this->mockPromotionalCollectionListCall(1, 1, 200, true, [], ['article/1234', 'interview/5678']);
        $this->mockPromotionalCollectionListCall(1, 100, 200, true, [], ['article/1234', 'interview/5678']);
        $this->mockPromotionalCollectionListCall(2, 100, 200, true, [], ['article/1234', 'interview/5678']);

        $this->promotionalCollections->containing(Identifier::article('1234'), Identifier::interview('5678'))->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $values = $this->promotionalCollections->prepend('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['foo', 'bar', '1', '2', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $values = $this->promotionalCollections->append('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['1', '2', '3', '4', '5', 'foo', 'bar'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $values = $this->promotionalCollections->drop(2)->map($this->tidyValue());

        $this->assertSame(['1', '2', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $values = $this->promotionalCollections->insert(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $values = $this->promotionalCollections->set(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '4', '5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockPromotionalCollectionListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->promotionalCollections->slice($offset, $length) as $i => $promotionalCollection) {
            $this->assertInstanceOf(PromotionalCollection::class, $promotionalCollection);
            $this->assertSame($expected[$i], $promotionalCollection->getId());
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
        $this->mockPromotionalCollectionListCall(1, 1, 3);
        $this->mockPromotionalCollectionListCall(1, 100, 3);

        $map = function (PromotionalCollection $promotionalCollection) {
            return $promotionalCollection->getId();
        };

        $this->assertSame(
            ['1', '2', '3'],
            $this->promotionalCollections->map($map)->toArray()
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $filter = function (PromotionalCollection $podcastEpisode) {
            return $podcastEpisode->getId() > 3;
        };

        foreach ($this->promotionalCollections->filter($filter) as $i => $podcastEpisode) {
            $this->assertSame((string) ($i + 4), $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $reduce = function (int $carry = null, PromotionalCollection $podcastEpisode) {
            return $carry + $podcastEpisode->getId();
        };

        $this->assertSame(115, $this->promotionalCollections->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->promotionalCollections, $this->promotionalCollections->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5);
        $this->mockPromotionalCollectionListCall(1, 100, 5);

        $sort = function (PromotionalCollection $a, PromotionalCollection $b) {
            return $b->getId() <=> $a->getId();
        };

        foreach ($this->promotionalCollections->sort($sort) as $i => $podcastEpisode) {
            $this->assertSame((string) (5 - $i), $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 5, false);
        $this->mockPromotionalCollectionListCall(1, 100, 5, false);

        foreach ($this->promotionalCollections->reverse() as $i => $podcastEpisode) {
            $this->assertSame((string) $i, $podcastEpisode->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 10);

        $this->promotionalCollections->count();

        $this->assertSame(10, $this->promotionalCollections->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockPromotionalCollectionListCall(1, 1, 200);
        $this->mockPromotionalCollectionListCall(1, 100, 200);
        $this->mockPromotionalCollectionListCall(2, 100, 200);

        $this->promotionalCollections->toArray();

        $this->mockPromotionalCollectionListCall(1, 1, 200, false);
        $this->mockPromotionalCollectionListCall(1, 100, 200, false);
        $this->mockPromotionalCollectionListCall(2, 100, 200, false);

        $this->promotionalCollections->reverse()->toArray();
    }
}
