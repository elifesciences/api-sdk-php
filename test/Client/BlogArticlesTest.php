<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class BlogArticlesTest extends ApiTestCase
{
    /** @var BlogArticles */
    private $blogArticles;

    /**
     * @before
     */
    protected function setUpBlogArticles()
    {
        $this->blogArticles = (new ApiSdk($this->getHttpClient()))->blogArticles();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->blogArticles);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockBlogArticleListCall(1, 1, 200);
        $this->mockBlogArticleListCall(1, 100, 200);
        $this->mockBlogArticleListCall(2, 100, 200);

        foreach ($this->blogArticles as $i => $blogArticle) {
            $this->assertInstanceOf(BlogArticle::class, $blogArticle);
            $this->assertSame('blogArticle'.$i, $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockBlogArticleListCall(1, 1, 10);

        $this->assertSame(10, $this->blogArticles->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockBlogArticleListCall(1, 1, 10);
        $this->mockBlogArticleListCall(1, 100, 10);

        $array = $this->blogArticles->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $blogArticle) {
            $this->assertInstanceOf(BlogArticle::class, $blogArticle);
            $this->assertSame('blogArticle'.($i + 1), $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_gets_a_blog_article()
    {
        $this->mockBlogArticleCall(7, true);

        $blogArticle = $this->blogArticles->get('blogArticle7')->wait();

        $this->assertInstanceOf(BlogArticle::class, $blogArticle);
        $this->assertSame('blogArticle7', $blogArticle->getId());

        $this->assertInstanceOf(Paragraph::class, $blogArticle->getContent()->toArray()[0]);
        $this->assertSame('Blog article blogArticle7 text', $blogArticle->getContent()->toArray()[0]->getText());

        $this->assertInstanceOf(Subject::class, $blogArticle->getSubjects()->toArray()[0]);
        $this->assertSame('Subject 1 name', $blogArticle->getSubjects()->toArray()[0]->getName());

        $this->mockSubjectCall('1');

        $this->assertSame('Subject 1 impact statement',
            $blogArticle->getSubjects()->toArray()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_blog_articles()
    {
        $this->mockBlogArticleListCall(1, 1, 1);
        $this->mockBlogArticleListCall(1, 100, 1);

        $this->blogArticles->toArray();

        $blogArticle = $this->blogArticles->get('blogArticle1')->wait();

        $this->assertInstanceOf(BlogArticle::class, $blogArticle);
        $this->assertSame('blogArticle1', $blogArticle->getId());

        $this->mockBlogArticleCall(1);

        $this->assertInstanceOf(Paragraph::class, $blogArticle->getContent()->toArray()[0]);
        $this->assertSame('Blog article blogArticle1 text', $blogArticle->getContent()->toArray()[0]->getText());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockBlogArticleListCall(1, 1, 5, true, ['subject']);
        $this->mockBlogArticleListCall(1, 100, 5, true, ['subject']);

        foreach ($this->blogArticles->forSubject('subject') as $i => $blogArticle) {
            $this->assertSame('blogArticle'.$i, $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_subject()
    {
        $this->mockBlogArticleListCall(1, 1, 10);

        $this->blogArticles->count();

        $this->mockBlogArticleListCall(1, 1, 10, true, ['subject']);

        $this->assertSame(10, $this->blogArticles->forSubject('subject')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering_by_subject()
    {
        $this->mockBlogArticleListCall(1, 1, 200);
        $this->mockBlogArticleListCall(1, 100, 200);
        $this->mockBlogArticleListCall(2, 100, 200);

        $this->blogArticles->toArray();

        $this->mockBlogArticleListCall(1, 1, 200, true, ['subject']);
        $this->mockBlogArticleListCall(1, 100, 200, true, ['subject']);
        $this->mockBlogArticleListCall(2, 100, 200, true, ['subject']);

        $this->blogArticles->forSubject('subject')->toArray();
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockBlogArticleListCall($call['page'], $call['per-page'], 5);
        }

        foreach ($this->blogArticles->slice($offset, $length) as $i => $blogArticle) {
            $this->assertInstanceOf(BlogArticle::class, $blogArticle);
            $this->assertSame('blogArticle'.($expected[$i]), $blogArticle->getId());
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
        $this->mockBlogArticleListCall(1, 1, 3);
        $this->mockBlogArticleListCall(1, 100, 3);

        $map = function (BlogArticle $blogArticle) {
            return $blogArticle->getId();
        };

        $this->assertSame(['blogArticle1', 'blogArticle2', 'blogArticle3'], $this->blogArticles->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $filter = function (BlogArticle $blogArticle) {
            return substr($blogArticle->getId(), -1) > 3;
        };

        foreach ($this->blogArticles->filter($filter) as $i => $blogArticle) {
            $this->assertSame('blogArticle'.($i + 4), $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $reduce = function (int $carry = null, BlogArticle $blogArticle) {
            return $carry + substr($blogArticle->getId(), -1);
        };

        $this->assertSame(115, $this->blogArticles->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $sort = function (BlogArticle $a, BlogArticle $b) {
            return substr($b->getId(), -1) <=> substr($a->getId(), -1);
        };

        foreach ($this->blogArticles->sort($sort) as $i => $blogArticle) {
            $this->assertSame('blogArticle'.(5 - $i), $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockBlogArticleListCall(1, 1, 5, false);
        $this->mockBlogArticleListCall(1, 100, 5, false);

        foreach ($this->blogArticles->reverse() as $i => $blogArticle) {
            $this->assertSame('blogArticle'.$i, $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockBlogArticleListCall(1, 1, 10);

        $this->blogArticles->count();

        $this->assertSame(10, $this->blogArticles->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockBlogArticleListCall(1, 1, 200);
        $this->mockBlogArticleListCall(1, 100, 200);
        $this->mockBlogArticleListCall(2, 100, 200);

        $this->blogArticles->toArray();

        $this->mockBlogArticleListCall(1, 1, 200, false);
        $this->mockBlogArticleListCall(1, 100, 200, false);
        $this->mockBlogArticleListCall(2, 100, 200, false);

        $this->blogArticles->reverse()->toArray();
    }
}
