<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiSdk\ApiClient\CommunityClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Community;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use test\eLife\ApiSdk\ApiTestCase;

final class CommunityTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Community */
    private $community;

    /**
     * @before
     */
    protected function setUpCommunity()
    {
        $this->community = (new ApiSdk($this->getHttpClient()))->community();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->community);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockCommunityListCall(1, 1, 200);
        $this->mockCommunityListCall(1, 100, 200);
        $this->mockCommunityListCall(2, 100, 200);

        foreach ($this->community as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
            $this->assertSame('model-'.$i, $model->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockCommunityListCall(1, 1, 10);

        $this->assertFalse($this->community->isEmpty());
        $this->assertSame(10, $this->community->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockCommunityListCall(1, 1, 10);
        $this->mockCommunityListCall(1, 100, 10);

        $array = $this->community->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
            $this->assertSame('model-'.($i + 1), $model->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockCommunityListCall(1, 1, 1);

        $this->assertTrue(isset($this->community[0]));
        $this->assertSame('model-1', $this->community[0]->getId());

        $this->mockNotFound(
            'community?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, 1)]
        );

        $this->assertFalse(isset($this->community[5]));
        $this->assertSame(null, $this->community[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->community[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockCommunityListCall(1, 1, 5, true, ['subject']);
        $this->mockCommunityListCall(1, 100, 5, true, ['subject']);

        foreach ($this->community->forSubject('subject') as $i => $model) {
            $this->assertSame('model-'.$i, $model->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockCommunityListCall(1, 1, 10);

        $this->community->count();

        $this->mockCommunityListCall(1, 1, 10, true, ['subject']);

        $this->assertSame(10, $this->community->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockCommunityListCall(1, 1, 200);
        $this->mockCommunityListCall(1, 100, 200);
        $this->mockCommunityListCall(2, 100, 200);

        $this->community->toArray();

        $this->mockCommunityListCall(1, 1, 200, true, ['subject']);
        $this->mockCommunityListCall(1, 100, 200, true, ['subject']);
        $this->mockCommunityListCall(2, 100, 200, true, ['subject']);

        $this->community->forSubject('subject')->toArray();
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $values = $this->community->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'model-1', 'model-2', 'model-3', 'model-4', 'model-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $values = $this->community->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['model-1', 'model-2', 'model-3', 'model-4', 'model-5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $values = $this->community->drop(2)->map($this->tidyValue());

        $this->assertSame(['model-1', 'model-2', 'model-4', 'model-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $values = $this->community->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['model-1', 'model-2', 2, 'model-3', 'model-4', 'model-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $values = $this->community->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['model-1', 'model-2', 2, 'model-4', 'model-5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockCommunityListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->community->slice($offset, $length) as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
            $this->assertSame('model-'.($expected[$i]), $model->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockCommunityListCall(1, 1, 3);
        $this->mockCommunityListCall(1, 100, 3);

        $map = function (Model $model) {
            return $model->getId();
        };

        $this->assertSame(['model-1', 'model-2', 'model-3'], $this->community->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $filter = function (Model $model) {
            return substr($model->getId(), -1) > 3;
        };

        foreach ($this->community->filter($filter) as $i => $model) {
            $this->assertSame('model-'.($i + 4), $model->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $reduce = function (int $carry = null, Model $model) {
            return $carry + substr($model->getId(), -1);
        };

        $this->assertSame(115, $this->community->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->community, $this->community->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockCommunityListCall(1, 1, 5);
        $this->mockCommunityListCall(1, 100, 5);

        $sort = function (Model $a, Model $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->community->sort($sort) as $i => $model) {
            $this->assertSame('model-'.(5 - $i), $model->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockCommunityListCall(1, 1, 5, false);
        $this->mockCommunityListCall(1, 100, 5, false);

        foreach ($this->community->reverse() as $i => $model) {
            $this->assertSame('model-'.$i, $model->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockCommunityListCall(1, 1, 10);

        $this->community->count();

        $this->assertSame(10, $this->community->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockCommunityListCall(1, 1, 200);
        $this->mockCommunityListCall(1, 100, 200);
        $this->mockCommunityListCall(2, 100, 200);

        $this->community->toArray();

        $this->mockCommunityListCall(1, 1, 200, false);
        $this->mockCommunityListCall(1, 100, 200, false);
        $this->mockCommunityListCall(2, 100, 200, false);

        $this->community->reverse()->toArray();
    }
}
