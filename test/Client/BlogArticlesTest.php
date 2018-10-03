<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Subject;
use test\eLife\ApiSdk\ApiTestCase;

final class BlogArticlesTest extends ApiTestCase
{
    use SlicingTestCase;

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
            $this->assertSame('blog-article-'.$i, $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockBlogArticleListCall(1, 1, 10);

        $this->assertFalse($this->blogArticles->isEmpty());
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
            $this->assertSame('blog-article-'.($i + 1), $blogArticle->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockBlogArticleListCall(1, 1, 1);

        $this->assertTrue(isset($this->blogArticles[0]));
        $this->assertSame('blog-article-1', $this->blogArticles[0]->getId());

        $this->mockNotFound(
            'blog-articles?page=6&per-page=1&order=desc',
            ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, BlogArticles::VERSION_BLOG_ARTICLE_LIST)]
        );

        $this->assertFalse(isset($this->blogArticles[5]));
        $this->assertSame(null, $this->blogArticles[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->blogArticles[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_gets_a_blog_article()
    {
        $this->mockBlogArticleCall(7, true);

        $blogArticle = $this->blogArticles->get('blog-article-7')->wait();

        $this->assertInstanceOf(BlogArticle::class, $blogArticle);
        $this->assertSame('blog-article-7', $blogArticle->getId());

        $this->assertInstanceOf(Paragraph::class, $blogArticle->getContent()[0]);
        $this->assertSame('Blog article blog-article-7 text', $blogArticle->getContent()[0]->getText());

        $this->assertInstanceOf(Subject::class, $blogArticle->getSubjects()[0]);
        $this->assertSame('Subject 1 name', $blogArticle->getSubjects()[0]->getName());

        $this->mockSubjectCall('1');

        $this->assertSame('Subject 1 impact statement',
            $blogArticle->getSubjects()[0]->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockBlogArticleListCall(1, 1, 5, true, ['subject']);
        $this->mockBlogArticleListCall(1, 100, 5, true, ['subject']);

        foreach ($this->blogArticles->forSubject('subject') as $i => $blogArticle) {
            $this->assertSame('blog-article-'.$i, $blogArticle->getId());
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
     */
    public function it_can_be_prepended()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $values = $this->blogArticles->prepend(0, 1)->map($this->tidyValue());

        $this->assertSame([0, 1, 'blog-article-1', 'blog-article-2', 'blog-article-3', 'blog-article-4', 'blog-article-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $values = $this->blogArticles->append(0, 1)->map($this->tidyValue());

        $this->assertSame(['blog-article-1', 'blog-article-2', 'blog-article-3', 'blog-article-4', 'blog-article-5', 0, 1], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $values = $this->blogArticles->drop(2)->map($this->tidyValue());

        $this->assertSame(['blog-article-1', 'blog-article-2', 'blog-article-4', 'blog-article-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $values = $this->blogArticles->insert(2, 2)->map($this->tidyValue());

        $this->assertSame(['blog-article-1', 'blog-article-2', 2, 'blog-article-3', 'blog-article-4', 'blog-article-5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockBlogArticleListCall(1, 1, 5);
        $this->mockBlogArticleListCall(1, 100, 5);

        $values = $this->blogArticles->set(2, 2)->map($this->tidyValue());

        $this->assertSame(['blog-article-1', 'blog-article-2', 2, 'blog-article-4', 'blog-article-5'], $values->toArray());
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
            $this->assertSame('blog-article-'.($expected[$i]), $blogArticle->getId());
        }
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

        $this->assertSame(['blog-article-1', 'blog-article-2', 'blog-article-3'], $this->blogArticles->map($map)->toArray());
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
            $this->assertSame('blog-article-'.($i + 4), $blogArticle->getId());
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

        $this->assertSame(115, $this->blogArticles->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->blogArticles, $this->blogArticles->flatten());
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
            $this->assertSame('blog-article-'.(5 - $i), $blogArticle->getId());
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
            $this->assertSame('blog-article-'.$i, $blogArticle->getId());
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
