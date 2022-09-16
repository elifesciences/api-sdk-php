<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\ReviewedPreprintsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\ReviewedPreprints;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class ReviewedPreprintsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var ReviewedPreprints */
    private $reviewedPreprints;

    /**
     * @before
     */
    protected function setUpReviewedPreprints()
    {
        $this->reviewedPreprints = (new ApiSdk($this->getHttpClient()))->reviewedPreprints();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->reviewedPreprints);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockReviewedPreprintListCall(1, 1, 200);
        $this->mockReviewedPreprintListCall(1, 100, 200);
        $this->mockReviewedPreprintListCall(2, 100, 200);

        foreach ($this->reviewedPreprints as $i => $reviewedPreprint) {
            $this->assertInstanceOf(ReviewedPreprint::class, $reviewedPreprint);
            $this->assertSame('reviewed-preprint-'.$i, $reviewedPreprint->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockReviewedPreprintListCall(1, 1, 10);

        $this->assertFalse($this->reviewedPreprints->isEmpty());
        $this->assertSame(10, $this->reviewedPreprints->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockReviewedPreprintListCall(1, 1, 10);
        $this->mockReviewedPreprintListCall(1, 100, 10);

        $array = $this->reviewedPreprints->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $reviewedPreprint) {
            $this->assertInstanceOf(ReviewedPreprint::class, $reviewedPreprint);
            $this->assertSame('reviewed-preprint-'.($i + 1), $reviewedPreprint->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockReviewedPreprintListCall(1, 1, 1);

        $this->assertTrue(isset($this->reviewedPreprints[0]));
        $this->assertSame('reviewed-preprint-1', $this->reviewedPreprints[0]->getId());

        $this->mockNotFound(
            'reviewed-preprints?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT_LIST, 1)]
        );

        $this->assertFalse(isset($this->reviewedPreprints[5]));
        $this->assertSame(null, $this->reviewedPreprints[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->reviewedPreprints[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $values = $this->reviewedPreprints->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'reviewed-preprint-1', 'reviewed-preprint-2', 'reviewed-preprint-3', 'reviewed-preprint-4', 'reviewed-preprint-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $values = $this->reviewedPreprints->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['reviewed-preprint-1', 'reviewed-preprint-2', 'reviewed-preprint-3', 'reviewed-preprint-4', 'reviewed-preprint-5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $values = $this->reviewedPreprints->drop(2)->map($this->tidyValue());

        $this->assertSame([
            'reviewed-preprint-1', 'reviewed-preprint-2', 'reviewed-preprint-4', 'reviewed-preprint-5'
        ], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $values = $this->reviewedPreprints->insert(2, 2)->map($this->tidyValue());

        $this->assertSame([
            'reviewed-preprint-1', 'reviewed-preprint-2', 2, 'reviewed-preprint-3', 'reviewed-preprint-4', 'reviewed-preprint-5'
        ], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $values = $this->reviewedPreprints->set(2, 2)->map($this->tidyValue());

        $this->assertSame([
            'reviewed-preprint-1', 'reviewed-preprint-2', 2, 'reviewed-preprint-4', 'reviewed-preprint-5'
        ], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockReviewedPreprintListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->reviewedPreprints->slice($offset, $length) as $i => $reviewedPreprint) {
            $this->assertInstanceOf(ReviewedPreprint::class, $reviewedPreprint);
            $this->assertSame('reviewed-preprint-'.($expected[$i]), $reviewedPreprint->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockReviewedPreprintListCall(1, 1, 3);
        $this->mockReviewedPreprintListCall(1, 100, 3);

        $map = function (ReviewedPreprint $reviewedPreprint) {
            return $reviewedPreprint->getId();
        };

        $this->assertSame(['reviewed-preprint-1', 'reviewed-preprint-2', 'reviewed-preprint-3'], $this->reviewedPreprints->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $filter = function (ReviewedPreprint $reviewedPreprint) {
            return substr($reviewedPreprint->getId(), -1) > 3;
        };

        foreach ($this->reviewedPreprints->filter($filter) as $i => $reviewedPreprint) {
            $this->assertSame('reviewed-preprint-'.($i + 4), $reviewedPreprint->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $reduce = function (int $carry = null, ReviewedPreprint $reviewedPreprint) {
            return $carry + substr($reviewedPreprint->getId(), -1);
        };

        $this->assertSame(115, $this->reviewedPreprints->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->reviewedPreprints, $this->reviewedPreprints->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5);
        $this->mockReviewedPreprintListCall(1, 100, 5);

        $sort = function (ReviewedPreprint $a, ReviewedPreprint $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->reviewedPreprints->sort($sort) as $i => $reviewedPreprint) {
            $this->assertSame('reviewed-preprint-'.(5 - $i), $reviewedPreprint->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockReviewedPreprintListCall(1, 1, 5, false);
        $this->mockReviewedPreprintListCall(1, 100, 5, false);

        foreach ($this->reviewedPreprints->reverse() as $i => $reviewedPreprint) {
            $this->assertSame('reviewed-preprint-'.$i, $reviewedPreprint->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockReviewedPreprintListCall(1, 1, 10);

        $this->reviewedPreprints->count();

        $this->assertSame(10, $this->reviewedPreprints->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockReviewedPreprintListCall(1, 1, 200);
        $this->mockReviewedPreprintListCall(1, 100, 200);
        $this->mockReviewedPreprintListCall(2, 100, 200);

        $this->reviewedPreprints->toArray();

        $this->mockReviewedPreprintListCall(1, 1, 200, false);
        $this->mockReviewedPreprintListCall(1, 100, 200, false);
        $this->mockReviewedPreprintListCall(2, 100, 200, false);

        $this->reviewedPreprints->reverse()->toArray();
    }
}
