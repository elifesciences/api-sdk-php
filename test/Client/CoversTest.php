<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CoversClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Covers;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Cover;
use test\eLife\ApiSdk\ApiTestCase;

final class CoversTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Covers */
    private $covers;

    /**
     * @before
     */
    protected function setUpCovers()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->covers = new Covers(
            new CoversClient($this->getHttpClient()),
            $apiSdk->getSerializer()
        );
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->covers);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockCoverListCall(1, 1, 200);
        $this->mockCoverListCall(1, 100, 200);
        $this->mockCoverListCall(2, 100, 200);

        foreach ($this->covers as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame('Cover '.$i.' title', $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockCoverListCall(1, 1, 10);

        $this->assertFalse($this->covers->isEmpty());
        $this->assertSame(10, $this->covers->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockCoverListCall(1, 1, 10);
        $this->mockCoverListCall(1, 100, 10);

        $array = $this->covers->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame('Cover '.($i + 1).' title', $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockCoverListCall(1, 1, 1);

        $this->assertTrue(isset($this->covers[0]));
        $this->assertSame('Cover 1 title', $this->covers[0]->getTitle());

        $this->mockNotFound(
            'covers?page=6&per-page=1&sort=date&order=desc&use-date=default',
            ['Accept' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, 1)]
        );

        $this->assertFalse(isset($this->covers[5]));
        $this->assertSame(null, $this->covers[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->covers[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_use_published_dates()
    {
        $this->mockCoverListCall(1, 1, 10, true, 'date', 'published');
        $this->mockCoverListCall(1, 100, 10, true, 'date', 'published');

        foreach ($this->covers->sortBy('date')->useDate('published') as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame("Cover $i title", $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_start_date()
    {
        $this->mockCoverListCall(1, 1, 10, true, 'date', 'default', new DateTimeImmutable('2017-01-02'));
        $this->mockCoverListCall(1, 100, 10, true, 'date', 'default', new DateTimeImmutable('2017-01-02'));

        foreach ($this->covers->startDate(new DateTimeImmutable('2017-01-02')) as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame("Cover $i title", $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_end_date()
    {
        $this->mockCoverListCall(1, 1, 10, true, 'date', 'default', null, new DateTimeImmutable('2017-01-02'));
        $this->mockCoverListCall(1, 100, 10, true, 'date', 'default', null, new DateTimeImmutable('2017-01-02'));

        foreach ($this->covers->endDate(new DateTimeImmutable('2017-01-02')) as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame("Cover $i title", $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering()
    {
        $this->mockCoverListCall(1, 1, 10);
        $this->covers->count();

        $this->mockCoverListCall(1, 1, 4, true, 'date', 'default', new DateTimeImmutable('2017-01-02'));
        $this->assertSame(4, $this->covers->startDate(new DateTimeImmutable('2017-01-02'))->count());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $values = $this->covers->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'Cover 1 title', 'Cover 2 title', 'Cover 3 title', 'Cover 4 title', 'Cover 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $values = $this->covers->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['Cover 1 title', 'Cover 2 title', 'Cover 3 title', 'Cover 4 title', 'Cover 5 title', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $values = $this->covers->drop(2)->map($this->tidyValue());

        $this->assertSame(['Cover 1 title', 'Cover 2 title', 'Cover 4 title', 'Cover 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $values = $this->covers->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['Cover 1 title', 'Cover 2 title', 2, 'Cover 3 title', 'Cover 4 title', 'Cover 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $values = $this->covers->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['Cover 1 title', 'Cover 2 title', 2, 'Cover 4 title', 'Cover 5 title'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockCoverListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->covers->slice($offset, $length) as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame('Cover '.($expected[$i]).' title', $cover->getTitle());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockCoverListCall(1, 1, 3);
        $this->mockCoverListCall(1, 100, 3);

        $map = function (Cover $cover) {
            return $cover->getTitle();
        };

        $this->assertSame(['Cover 1 title', 'Cover 2 title', 'Cover 3 title'],
            $this->covers->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $filter = function (Cover $cover) {
            preg_match('/^Cover ([0-9]+) title$/', $cover->getTitle(), $matches);

            return $matches[1] > 3;
        };

        foreach ($this->covers->filter($filter) as $i => $cover) {
            $this->assertSame('Cover '.($i + 4).' title', $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $reduce = function (int $carry = null, Cover $cover) {
            preg_match('/^Cover ([0-9]+) title$/', $cover->getTitle(), $matches);

            return $carry + $matches[1];
        };

        $this->assertSame(115, $this->covers->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->covers, $this->covers->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted_by_page_views()
    {
        $this->mockCoverListCall(1, 1, 10, true, 'page-views');
        $this->mockCoverListCall(1, 100, 10, true, 'page-views');

        foreach ($this->covers->sortBy('page-views') as $i => $cover) {
            $this->assertSame("Cover $i title", $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockCoverListCall(1, 1, 5);
        $this->mockCoverListCall(1, 100, 5);

        $sort = function (Cover $a, Cover $b) {
            preg_match('/^Cover ([0-9]+) title$/', $a->getTitle(), $matchesA);
            preg_match('/^Cover ([0-9]+) title$/', $b->getTitle(), $matchesB);

            return $matchesB[1] <=> $matchesA[1];
        };

        foreach ($this->covers->sort($sort) as $i => $cover) {
            $this->assertSame('Cover '.(5 - $i).' title', $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockCoverListCall(1, 1, 5, false);
        $this->mockCoverListCall(1, 100, 5, false);

        foreach ($this->covers->reverse() as $i => $cover) {
            $this->assertSame('Cover '.$i.' title', $cover->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockCoverListCall(1, 1, 10);

        $this->covers->count();

        $this->assertSame(10, $this->covers->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockCoverListCall(1, 1, 200);
        $this->mockCoverListCall(1, 100, 200);
        $this->mockCoverListCall(2, 100, 200);

        $this->covers->toArray();

        $this->mockCoverListCall(1, 1, 200, false);
        $this->mockCoverListCall(1, 100, 200, false);
        $this->mockCoverListCall(2, 100, 200, false);

        $this->covers->reverse()->toArray();
    }

    /**
     * @test
     */
    public function it_has_current_covers()
    {
        $this->mockCurrentCoverListCall(3);

        $expected = range(3, 1);

        foreach ($this->covers->getCurrent() as $i => $cover) {
            $this->assertInstanceOf(Cover::class, $cover);
            $this->assertSame('Cover '.($expected[$i]).' title', $cover->getTitle());
        }
    }
}
