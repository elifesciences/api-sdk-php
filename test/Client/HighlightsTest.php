<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\HighlightsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Highlights;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Highlight;
use test\eLife\ApiSdk\ApiTestCase;

final class HighlightsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Highlights */
    private $highlights;

    /**
     * @before
     */
    protected function setUpSearch()
    {
        $this->highlights = (new ApiSdk($this->getHttpClient()))->highlights();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $list = $this->highlights->get('foo');

        $this->assertInstanceOf(Sequence::class, $list);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 200);
        $this->mockHighlightsCall('foo', 1, 100, 200);
        $this->mockHighlightsCall('foo', 2, 100, 200);

        $this->assertSame(200, $this->traverseAndSanityCheck($this->highlights->get('foo')));
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 10);

        $this->assertFalse($list->isEmpty());
        $this->assertSame(10, $list->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 10);
        $this->mockHighlightsCall('foo', 1, 100, 10);

        $this->assertSame(10, $this->traverseAndSanityCheck($list->toArray()));
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 1);

        $this->assertTrue(isset($list[0]));
        $this->assertSame('Highlight 1 title', $list[0]->getTitle());

        $this->mockNotFound(
            'highlights/foo?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(HighlightsClient::TYPE_HIGHLIGHT_LIST, 1)]
        );

        $this->assertFalse(isset($list[5]));
        $this->assertSame(null, $list[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $list = $this->highlights->get('foo');

        $this->expectException(BadMethodCallException::class);

        $list[0] = 'foo';
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        $list = $this->highlights->get('foo');

        foreach ($calls as $call) {
            $this->mockHighlightsCall('foo', $call['page'], $call['per-page'], 5);
        }

        foreach ($list->slice($offset, $length) as $i => $highlight) {
            $this->assertInstanceOf(Highlight::class, $highlight);
            $this->assertSame("Highlight {$expected[$i]} title", $highlight->getTitle());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 3);
        $this->mockHighlightsCall('foo', 1, 100, 3);

        $map = function (Highlight $highlight) {
            return $highlight->getTitle();
        };

        $this->assertSame(['Highlight 1 title', 'Highlight 2 title', 'Highlight 3 title'], $list->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 5);
        $this->mockHighlightsCall('foo', 1, 100, 5);

        $filter = function (Highlight $highlight, int $key) {
            return $key >= 3;
        };

        foreach ($list->filter($filter) as $i => $highlight) {
            $expected = $i + 4;
            $this->assertSame("Highlight $expected title", $highlight->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 5);
        $this->mockHighlightsCall('foo', 1, 100, 5);

        $reduce = function (int $carry = null, Highlight $highlight) {
            return $carry + explode(' ', $highlight->getTitle())[1];
        };

        $this->assertSame(115, $list->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 5);
        $this->mockHighlightsCall('foo', 1, 100, 5);

        $sort = function (Highlight $a, Highlight $b) {
            return $b->getTitle() <=> $a->getTitle();
        };

        foreach ($list->sort($sort) as $i => $highlight) {
            $expected = 5 - $i;
            $this->assertSame("Highlight $expected title", $highlight->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 5, false);
        $this->mockHighlightsCall('foo', 1, 100, 5, false);

        foreach ($list->reverse() as $i => $highlight) {
            $this->assertSame("Highlight $i title", $highlight->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 10);

        $list->count();

        $this->assertSame(10, $list->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $list = $this->highlights->get('foo');

        $this->mockHighlightsCall('foo', 1, 1, 200);
        $this->mockHighlightsCall('foo', 1, 100, 200);
        $this->mockHighlightsCall('foo', 2, 100, 200);

        $list->toArray();

        $this->mockHighlightsCall('foo', 1, 1, 200, false);
        $this->mockHighlightsCall('foo', 1, 100, 200, false);
        $this->mockHighlightsCall('foo', 2, 100, 200, false);

        $list->reverse()->toArray();
    }

    private function traverseAndSanityCheck($search)
    {
        $count = 0;
        foreach ($search as $i => $model) {
            $this->assertInstanceOf(Highlight::class, $model);
            ++$count;
        }

        return $count;
    }
}
