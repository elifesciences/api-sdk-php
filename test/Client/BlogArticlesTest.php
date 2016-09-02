<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection;
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
        $this->blogArticles = new BlogArticles(
            new BlogClient($this->getHttpClient()),
            new Subjects(new SubjectsClient($this->getHttpClient()))
        );
    }

    /**
     * @test
     */
    public function it_is_a_collection()
    {
        $this->assertInstanceOf(Collection::class, $this->blogArticles);
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

        $this->assertInstanceOf(Paragraph::class, $blogArticle->getContent()[0]);
        $this->assertSame('Blog article 7 text', $blogArticle->getContent()[0]->getText());

        $this->mockSubjectCall(1);

        $this->assertInstanceOf(Subject::class, $blogArticle->getSubjects()[0]);
        $this->assertSame('Subject 1 name', $blogArticle->getSubjects()[0]->getName());
    }

    /**
     * @test
     */
    public function it_reuses_already_known_blog_articles()
    {
        $this->mockBlogArticleListCall(1, 1, 10);
        $this->mockBlogArticleListCall(1, 100, 10);

        $this->blogArticles->toArray();

        $blogArticle = $this->blogArticles->get('blogArticle7')->wait();

        $this->assertInstanceOf(BlogArticle::class, $blogArticle);
        $this->assertSame('blogArticle7', $blogArticle->getId());
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
}
