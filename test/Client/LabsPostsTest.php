<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\LabsPosts;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\LabsPost;
use test\eLife\ApiSdk\ApiTestCase;

final class LabsPostsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var LabsPosts */
    private $labsPosts;

    /**
     * @before
     */
    protected function setUpLabsPosts()
    {
        $this->labsPosts = (new ApiSdk($this->getHttpClient()))->labsPosts();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->labsPosts);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockLabsPostListCall(1, 1, 200);
        $this->mockLabsPostListCall(1, 100, 200);
        $this->mockLabsPostListCall(2, 100, 200);

        foreach ($this->labsPosts as $i => $labsPost) {
            $this->assertInstanceOf(LabsPost::class, $labsPost);
            $this->assertSame((string) $i, $labsPost->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockLabsPostListCall(1, 1, 10);

        $this->assertFalse($this->labsPosts->isEmpty());
        $this->assertSame(10, $this->labsPosts->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockLabsPostListCall(1, 1, 10);
        $this->mockLabsPostListCall(1, 100, 10);

        $array = $this->labsPosts->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $labsPost) {
            $this->assertInstanceOf(LabsPost::class, $labsPost);
            $this->assertSame((string) ($i + 1), $labsPost->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockLabsPostListCall(1, 1, 1);

        $this->assertTrue(isset($this->labsPosts[0]));
        $this->assertSame('1', $this->labsPosts[0]->getId());

        $this->mockNotFound(
            'labs-posts?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(LabsClient::TYPE_POST_LIST, 1)]
        );

        $this->assertFalse(isset($this->labsPosts[5]));
        $this->assertSame(null, $this->labsPosts[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->labsPosts[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_labs_post()
    {
        $this->mockLabsPostCall(7);

        $labsPost = $this->labsPosts->get(7)->wait();

        $this->assertInstanceOf(LabsPost::class, $labsPost);
        $this->assertSame('7', $labsPost->getId());

        $this->assertInstanceOf(Paragraph::class, $labsPost->getContent()[0]);
        $this->assertSame('Labs post 7 text', $labsPost->getContent()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $values = $this->labsPosts->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, '1', '2', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $values = $this->labsPosts->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['1', '2', '3', '4', '5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $values = $this->labsPosts->drop(2)->map($this->tidyValue());

        $this->assertSame(['1', '2', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $values = $this->labsPosts->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['1', '2', 2, '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $values = $this->labsPosts->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['1', '2', 2, '4', '5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockLabsPostListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->labsPosts->slice($offset, $length) as $i => $labsPost) {
            $this->assertInstanceOf(LabsPost::class, $labsPost);
            $this->assertSame((string) $expected[$i], $labsPost->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockLabsPostListCall(1, 1, 3);
        $this->mockLabsPostListCall(1, 100, 3);

        $map = function (LabsPost $labsPost) {
            return $labsPost->getId();
        };

        $this->assertSame(['1', '2', '3'], $this->labsPosts->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $filter = function (LabsPost $labsPost) {
            return substr($labsPost->getId(), -1) > 3;
        };

        foreach ($this->labsPosts->filter($filter) as $i => $labsPost) {
            $this->assertSame((string) ($i + 4), $labsPost->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $reduce = function (int $carry = null, LabsPost $labsPost) {
            return $carry + $labsPost->getId();
        };

        $this->assertSame(115, $this->labsPosts->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->labsPosts, $this->labsPosts->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockLabsPostListCall(1, 1, 5);
        $this->mockLabsPostListCall(1, 100, 5);

        $sort = function (LabsPost $a, LabsPost $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->labsPosts->sort($sort) as $i => $labsPost) {
            $this->assertSame((string) (5 - $i), $labsPost->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockLabsPostListCall(1, 1, 5, false);
        $this->mockLabsPostListCall(1, 100, 5, false);

        foreach ($this->labsPosts->reverse() as $i => $labsPost) {
            $this->assertSame((string) $i, $labsPost->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockLabsPostListCall(1, 1, 10);

        $this->labsPosts->count();

        $this->assertSame(10, $this->labsPosts->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockLabsPostListCall(1, 1, 200);
        $this->mockLabsPostListCall(1, 100, 200);
        $this->mockLabsPostListCall(2, 100, 200);

        $this->labsPosts->toArray();

        $this->mockLabsPostListCall(1, 1, 200, false);
        $this->mockLabsPostListCall(1, 100, 200, false);
        $this->mockLabsPostListCall(2, 100, 200, false);

        $this->labsPosts->reverse()->toArray();
    }
}
