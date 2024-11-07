<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiSdk\ApiClient\DigestsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Digests;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Digest;
use test\eLife\ApiSdk\ApiTestCase;

final class DigestsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Digests */
    private $digests;

    /**
     * @before
     */
    protected function setUpDigests()
    {
        $this->digests = (new ApiSdk($this->getHttpClient()))->digests();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->digests);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockDigestListCall(1, 1, 200);
        $this->mockDigestListCall(1, 100, 200);
        $this->mockDigestListCall(2, 100, 200);

        foreach ($this->digests as $i => $digest) {
            $this->assertInstanceOf(Digest::class, $digest);
            $this->assertSame('digest-'.$i, $digest->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockDigestListCall(1, 1, 10);

        $this->assertFalse($this->digests->isEmpty());
        $this->assertSame(10, $this->digests->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockDigestListCall(1, 1, 10);
        $this->mockDigestListCall(1, 100, 10);

        $array = $this->digests->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $digest) {
            $this->assertInstanceOf(Digest::class, $digest);
            $this->assertSame('digest-'.($i + 1), $digest->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockDigestListCall(1, 1, 1);

        $this->assertTrue(isset($this->digests[0]));
        $this->assertSame('digest-1', $this->digests[0]->getId());

        $this->mockNotFound(
            'digests?page=6&per-page=1&order=desc',
            ['Accept' => (string) new MediaType(Digestsclient::TYPE_DIGEST_LIST, 1)]
        );

        $this->assertFalse(isset($this->digests[5]));
        $this->assertSame(null, $this->digests[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->digests[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_digest()
    {
        $this->mockDigestCall('digest-7', true);

        $digest = $this->digests->get('digest-7')->wait();

        $this->assertInstanceOf(Digest::class, $digest);
        $this->assertSame('digest-7', $digest->getId());

        $this->assertInstanceOf(Paragraph::class, $digest->getContent()[0]);
        $this->assertSame('Digest digest-7 text', $digest->getContent()[0]->getText());

        $this->assertInstanceOf(ArticleVoR::class, $digest->getRelatedContent()[0]);
        $this->assertContains('Homo naledi', $digest->getRelatedContent()[0]->getTitle());

        $this->mockArticleCall('09560', true, true, 1);

        $this->assertInstanceOf(Block::class, $digest->getRelatedContent()[0]->getContent()[0]);
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $values = $this->digests->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'digest-1', 'digest-2', 'digest-3', 'digest-4', 'digest-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $values = $this->digests->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['digest-1', 'digest-2', 'digest-3', 'digest-4', 'digest-5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $values = $this->digests->drop(2)->map($this->tidyValue());

        $this->assertSame(['digest-1', 'digest-2', 'digest-4', 'digest-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $values = $this->digests->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['digest-1', 'digest-2', 2, 'digest-3', 'digest-4', 'digest-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $values = $this->digests->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['digest-1', 'digest-2', 2, 'digest-4', 'digest-5'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockDigestListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->digests->slice($offset, $length) as $i => $digest) {
            $this->assertInstanceOf(Digest::class, $digest);
            $this->assertSame('digest-'.($expected[$i]), $digest->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockDigestListCall(1, 1, 3);
        $this->mockDigestListCall(1, 100, 3);

        $map = function (Digest $digest) {
            return $digest->getId();
        };

        $this->assertSame(['digest-1', 'digest-2', 'digest-3'], $this->digests->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $filter = function (Digest $digest) {
            return substr($digest->getId(), -1) > 3;
        };

        foreach ($this->digests->filter($filter) as $i => $digest) {
            $this->assertSame('digest-'.($i + 4), $digest->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $reduce = function (int $carry = null, Digest $digest) {
            return $carry + substr($digest->getId(), -1);
        };

        $this->assertSame(115, $this->digests->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->digests, $this->digests->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockDigestListCall(1, 1, 5);
        $this->mockDigestListCall(1, 100, 5);

        $sort = function (Digest $a, Digest $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->digests->sort($sort) as $i => $digest) {
            $this->assertSame('digest-'.(5 - $i), $digest->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockDigestListCall(1, 1, 5, false);
        $this->mockDigestListCall(1, 100, 5, false);

        foreach ($this->digests->reverse() as $i => $digest) {
            $this->assertSame('digest-'.$i, $digest->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockDigestListCall(1, 1, 10);

        $this->digests->count();

        $this->assertSame(10, $this->digests->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockDigestListCall(1, 1, 200);
        $this->mockDigestListCall(1, 100, 200);
        $this->mockDigestListCall(2, 100, 200);

        $this->digests->toArray();

        $this->mockDigestListCall(1, 1, 200, false);
        $this->mockDigestListCall(1, 100, 200, false);
        $this->mockDigestListCall(2, 100, 200, false);

        $this->digests->reverse()->toArray();
    }
}
