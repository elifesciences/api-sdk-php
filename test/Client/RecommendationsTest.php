<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\RecommendationsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Recommendations;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Identifier;
use test\eLife\ApiSdk\ApiTestCase;

final class RecommendationsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Recommendations */
    private $recommendations;

    /**
     * @before
     */
    protected function setUpRecommendations()
    {
        $this->recommendations = (new ApiSdk($this->getHttpClient()))->recommendations();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->assertInstanceOf(Sequence::class, $list);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 200);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 200);
        $this->mockRecommendationsCall('article', 'article1', 2, 100, 200);

        foreach ($list as $i => $article) {
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertEquals("article$i", $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 10);

        $this->assertFalse($list->isEmpty());
        $this->assertSame(10, $list->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 10);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 10);

        $array = $list->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $article) {
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertEquals('article'.($i + 1), $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 1);

        $this->assertTrue(isset($list[0]));
        $this->assertSame('article1', $list[0]->getId());

        $this->mockNotFound(
            'recommendations/article/article1?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(RecommendationsClient::TYPE_RECOMMENDATIONS, 1)]
        );

        $this->assertFalse(isset($list[5]));
        $this->assertSame(null, $list[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->expectException(BadMethodCallException::class);

        $list[0] = 'foo';
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        foreach ($calls as $call) {
            $this->mockRecommendationsCall('article', 'article1', $call['page'], $call['per-page'], 5);
        }

        foreach ($list->slice($offset, $length) as $i => $article) {
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertSame('article'.($expected[$i]), $article->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 3);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 3);

        $map = function (ArticleVersion $article) {
            return $article->getId();
        };

        $this->assertSame(['article1', 'article2', 'article3'], $list->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 5);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 5);

        $filter = function (ArticleVersion $article) {
            return substr($article->getId(), -1) > 3;
        };

        foreach ($list->filter($filter) as $i => $article) {
            $this->assertSame('article'.($i + 4), $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 5);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 5);

        $reduce = function (int $carry = null, ArticleVersion $article) {
            return $carry + substr($article->getId(), -1);
        };

        $this->assertSame(115, $list->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 5);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 5);

        $sort = function (ArticleVersion $a, ArticleVersion $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($list->sort($sort) as $i => $article) {
            $this->assertSame('article'.(5 - $i), $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 5, false);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 5, false);

        foreach ($list->reverse() as $i => $article) {
            $this->assertEquals("article$i", $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 10);

        $list->count();

        $this->assertSame(10, $list->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $list = $this->recommendations->list(Identifier::article('article1'));

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 200);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 200);
        $this->mockRecommendationsCall('article', 'article1', 2, 100, 200);

        $list->toArray();

        $this->mockRecommendationsCall('article', 'article1', 1, 1, 200, false);
        $this->mockRecommendationsCall('article', 'article1', 1, 100, 200, false);
        $this->mockRecommendationsCall('article', 'article1', 2, 100, 200, false);

        $list->reverse()->toArray();
    }
}
