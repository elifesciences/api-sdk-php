<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\Client\MediumArticles;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\MediumArticle;
use eLife\ApiSdk\Serializer\AssetFileNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\MediumArticleNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use test\eLife\ApiSdk\ApiTestCase;

final class MediumArticlesTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var MediumArticles */
    private $mediumArticles;

    /**
     * @before
     */
    protected function setUpMediumArticles()
    {
        $this->mediumArticles = new MediumArticles(
            new MediumClient($this->getHttpClient()),
            new NormalizerAwareSerializer([new MediumArticleNormalizer(), new ImageNormalizer(), new AssetFileNormalizer(), new FileNormalizer()])
        );
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->mediumArticles);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockMediumArticleListCall(1, 1, 200);
        $this->mockMediumArticleListCall(1, 100, 200);
        $this->mockMediumArticleListCall(2, 100, 200);

        foreach ($this->mediumArticles as $i => $mediumArticle) {
            $this->assertInstanceOf(MediumArticle::class, $mediumArticle);
            $this->assertSame('Medium article '.$i.' title', $mediumArticle->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockMediumArticleListCall(1, 1, 10);

        $this->assertFalse($this->mediumArticles->isEmpty());
        $this->assertSame(10, $this->mediumArticles->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockMediumArticleListCall(1, 1, 10);
        $this->mockMediumArticleListCall(1, 100, 10);

        $array = $this->mediumArticles->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $mediumArticle) {
            $this->assertInstanceOf(MediumArticle::class, $mediumArticle);
            $this->assertSame('Medium article '.($i + 1).' title', $mediumArticle->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockMediumArticleListCall(1, 1, 1);

        $this->assertTrue(isset($this->mediumArticles[0]));
        $this->assertSame('Medium article 1 title', $this->mediumArticles[0]->getTitle());

        $this->mockNotFound(
            'medium-articles?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(MediumClient::TYPE_MEDIUM_ARTICLE_LIST, 1)]
        );

        $this->assertFalse(isset($this->mediumArticles[5]));
        $this->assertSame(null, $this->mediumArticles[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->mediumArticles[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $values = $this->mediumArticles->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'Medium article 1 title', 'Medium article 2 title', 'Medium article 3 title', 'Medium article 4 title', 'Medium article 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $values = $this->mediumArticles->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['Medium article 1 title', 'Medium article 2 title', 'Medium article 3 title', 'Medium article 4 title', 'Medium article 5 title', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $values = $this->mediumArticles->drop(2)->map($this->tidyValue());

        $this->assertSame(['Medium article 1 title', 'Medium article 2 title', 'Medium article 4 title', 'Medium article 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $values = $this->mediumArticles->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['Medium article 1 title', 'Medium article 2 title', 2, 'Medium article 3 title', 'Medium article 4 title', 'Medium article 5 title'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $values = $this->mediumArticles->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['Medium article 1 title', 'Medium article 2 title', 2, 'Medium article 4 title', 'Medium article 5 title'], $values->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockMediumArticleListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->mediumArticles->slice($offset, $length) as $i => $mediumArticle) {
            $this->assertInstanceOf(MediumArticle::class, $mediumArticle);
            $this->assertSame('Medium article '.($expected[$i]).' title', $mediumArticle->getTitle());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $this->mockMediumArticleListCall(1, 1, 3);
        $this->mockMediumArticleListCall(1, 100, 3);

        $map = function (MediumArticle $mediumArticle) {
            return $mediumArticle->getTitle();
        };

        $this->assertSame(['Medium article 1 title', 'Medium article 2 title', 'Medium article 3 title'],
            $this->mediumArticles->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $filter = function (MediumArticle $mediumArticle) {
            return substr($mediumArticle->getUri(), -1) > 3;
        };

        foreach ($this->mediumArticles->filter($filter) as $i => $mediumArticle) {
            $this->assertSame('Medium article '.($i + 4).' title', $mediumArticle->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $reduce = function (int $carry = null, MediumArticle $mediumArticle) {
            return $carry + substr($mediumArticle->getUri(), -1);
        };

        $this->assertSame(115, $this->mediumArticles->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->mediumArticles, $this->mediumArticles->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockMediumArticleListCall(1, 1, 5);
        $this->mockMediumArticleListCall(1, 100, 5);

        $sort = function (MediumArticle $a, MediumArticle $b) {
            return substr($b->getUri(), -1) <=> substr($a->getUri(), -1);
        };

        foreach ($this->mediumArticles->sort($sort) as $i => $mediumArticle) {
            $this->assertSame('Medium article '.(5 - $i).' title', $mediumArticle->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockMediumArticleListCall(1, 1, 5, false);
        $this->mockMediumArticleListCall(1, 100, 5, false);

        foreach ($this->mediumArticles->reverse() as $i => $mediumArticle) {
            $this->assertSame('Medium article '.$i.' title', $mediumArticle->getTitle());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockMediumArticleListCall(1, 1, 10);

        $this->mediumArticles->count();

        $this->assertSame(10, $this->mediumArticles->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockMediumArticleListCall(1, 1, 200);
        $this->mockMediumArticleListCall(1, 100, 200);
        $this->mockMediumArticleListCall(2, 100, 200);

        $this->mediumArticles->toArray();

        $this->mockMediumArticleListCall(1, 1, 200, false);
        $this->mockMediumArticleListCall(1, 100, 200, false);
        $this->mockMediumArticleListCall(2, 100, 200, false);

        $this->mediumArticles->reverse()->toArray();
    }
}
