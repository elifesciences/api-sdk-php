<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class ArticlesTest extends ApiTestCase
{
    /** @var Articles */
    private $articles;

    /**
     * @before
     */
    protected function setUpArticles()
    {
        $this->articles = (new ApiSdk($this->getHttpClient()))->articles();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->articles);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockArticleListCall(1, 1, 200);
        $this->mockArticleListCall(1, 100, 200);
        $this->mockArticleListCall(2, 100, 200);

        foreach ($this->articles as $i => $article) {
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertSame('article'.$i, $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_make_unnecessary_network_requests()
    {
        // 3000 in full list.
        $this->mockArticleListCall(1, 1, 3000);
        $this->mockArticleListCall(1, 100, 3000);
        // Only grab first 50
        foreach ($this->articles as $i => $article) {
            if ($i > 50) {
                break;
            }
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertSame('article'.$i, $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockArticleListCall(1, 1, 10);

        $this->assertFalse($this->articles->isEmpty());
        $this->assertSame(10, $this->articles->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockArticleListCall(1, 1, 10);
        $this->mockArticleListCall(1, 100, 10);

        $array = $this->articles->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $article) {
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertSame('article'.($i + 1), $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockArticleListCall(1, 1, 1);

        $this->assertTrue(isset($this->articles[0]));
        $this->assertSame('article1', $this->articles[0]->getId());

        $this->mockNotFound(
            'articles?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)]
        );

        $this->assertFalse(isset($this->articles[5]));
        $this->assertSame(null, $this->articles[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->articles[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_an_article()
    {
        $this->mockArticleCall('article7', true, true);

        $article = $this->articles->get('article7')->wait();

        $this->assertInstanceOf(ArticleVersion::class, $article);
        $this->assertSame('article7', $article->getId());

        $this->assertInstanceOf(Section::class, $article->getContent()[0]);
        $this->assertSame('Article article7 section title', $article->getContent()[0]->getTitle());
        $this->assertInstanceOf(Paragraph::class, $article->getContent()[0]->getContent()[0]);
        $this->assertSame('Article article7 text', $article->getContent()[0]->getContent()[0]->getText());

        $this->mockSubjectCall(1);

        $this->assertInstanceOf(Subject::class, $article->getSubjects()[0]);
        $this->assertSame('Subject 1 name', $article->getSubjects()[0]->getName());
    }

    /**
     * @test
     */
    public function it_gets_an_article_history()
    {
        $this->mockArticleHistoryCall('article7', true);

        $articleHistory = $this->articles->getHistory('article7')->wait();

        $this->assertInstanceOf(ArticleHistory::class, $articleHistory);

        $this->mockArticleCall('article7', true, false, 1);
        $this->mockArticleCall('article7', true, true, 2);
        $this->mockSubjectCall(1);

        foreach ($articleHistory->getVersions() as $articleVersion) {
            $this->assertSame('article7', $articleVersion->getId());

            if ($articleVersion instanceof ArticleVoR) {
                $this->assertInstanceOf(Section::class, $articleVersion->getContent()[0]);
                $this->assertSame('Article article7 section title', $articleVersion->getContent()[0]->getTitle());
                $this->assertInstanceOf(Paragraph::class, $articleVersion->getContent()[0]->getContent()[0]);
                $this->assertSame('Article article7 text', $articleVersion->getContent()[0]->getContent()[0]->getText());
            }

            $this->assertInstanceOf(Subject::class, $articleVersion->getSubjects()[0]);
            $this->assertSame('Subject 1 name', $articleVersion->getSubjects()[0]->getName());
        }
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockArticleListCall(1, 1, 5, true, ['subject']);
        $this->mockArticleListCall(1, 100, 5, true, ['subject']);

        foreach ($this->articles->forSubject('subject') as $i => $article) {
            $this->assertSame('article'.$i, $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockArticleListCall(1, 1, 10);

        $this->articles->count();

        $this->mockArticleListCall(1, 1, 10, true, ['subject']);

        $this->assertSame(10, $this->articles->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockArticleListCall(1, 1, 200);
        $this->mockArticleListCall(1, 100, 200);
        $this->mockArticleListCall(2, 100, 200);

        $this->articles->toArray();

        $this->mockArticleListCall(1, 1, 200, true, ['subject']);
        $this->mockArticleListCall(1, 100, 200, true, ['subject']);
        $this->mockArticleListCall(2, 100, 200, true, ['subject']);

        $this->articles->forSubject('subject')->toArray();
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockArticleListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->articles->slice($offset, $length) as $i => $article) {
            $this->assertInstanceOf(ArticleVersion::class, $article);
            $this->assertSame('article'.($expected[$i]), $article->getId());
        }
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [
                1,
                1,
                [2],
                [
                    ['page' => 2, 'per-page' => 1],
                ],
            ],
            'offset -2, no length' => [
                -2,
                null,
                [4, 5],
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
        $this->mockArticleListCall(1, 1, 3);
        $this->mockArticleListCall(1, 100, 3);

        $map = function (ArticleVersion $article) {
            return $article->getId();
        };

        $this->assertSame(['article1', 'article2', 'article3'], $this->articles->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockArticleListCall(1, 1, 5);
        $this->mockArticleListCall(1, 100, 5);

        $filter = function (ArticleVersion $article) {
            return substr($article->getId(), -1) > 3;
        };

        foreach ($this->articles->filter($filter) as $i => $article) {
            $this->assertSame('article'.($i + 4), $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockArticleListCall(1, 1, 5);
        $this->mockArticleListCall(1, 100, 5);

        $reduce = function (int $carry = null, ArticleVersion $article) {
            return $carry + substr($article->getId(), -1);
        };

        $this->assertSame(115, $this->articles->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockArticleListCall(1, 1, 5);
        $this->mockArticleListCall(1, 100, 5);

        $sort = function (ArticleVersion $a, ArticleVersion $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->articles->sort($sort) as $i => $article) {
            $this->assertSame('article'.(5 - $i), $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockArticleListCall(1, 1, 5, false);
        $this->mockArticleListCall(1, 100, 5, false);

        foreach ($this->articles->reverse() as $i => $article) {
            $this->assertSame('article'.$i, $article->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockArticleListCall(1, 1, 10);

        $this->articles->count();

        $this->assertSame(10, $this->articles->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockArticleListCall(1, 1, 200);
        $this->mockArticleListCall(1, 100, 200);
        $this->mockArticleListCall(2, 100, 200);

        $this->articles->toArray();

        $this->mockArticleListCall(1, 1, 200, false);
        $this->mockArticleListCall(1, 100, 200, false);
        $this->mockArticleListCall(2, 100, 200, false);

        $this->articles->reverse()->toArray();
    }

    /**
     * @test
     */
    public function it_silently_skips_articles_marked_as_invalid_to_allow_bulk_imports_to_work()
    {
        $this->mockArticleListCall(1, 1, 100);
        $this->mockArticleListCallWithAnInvalidArticle(1, 100, 100);

        $count = $nullsCount = 0;
        foreach ($this->articles as $article) {
            ++$count;
            if ($article === null) {
                ++$nullsCount;
            }
        }
        $this->assertEquals(100, $count);
        $this->assertEquals(1, $nullsCount);
    }
}
