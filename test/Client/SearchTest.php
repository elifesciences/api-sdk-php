<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Model;
use test\eLife\ApiSdk\ApiTestCase;

class SearchTest extends ApiTestCase
{
    /** @var Search */
    private $search;

    /**
     * @before
     */
    protected function setUpSearch()
    {
        $this->search = (new ApiSdk($this->getHttpClient()))->search();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $this->assertInstanceOf(Sequence::class, $this->search);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $this->mockCountCall(200);
        $this->mockFirstPageCall(200);
        $this->mockSearchCall($page = 2, $perPage = 100, $total = 200);

        foreach ($this->search as $i => $result) {
            $this->assertInstanceOf(Model::class, $result);
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockCountCall(10);

        $this->assertSame(10, $this->search->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->mockCountCall(10);
        $this->mockFirstPageCall(10);

        $array = $this->search->toArray();

        $this->assertCount(10, $array);

        foreach ($array as $i => $result) {
            $this->assertInstanceOf(Model::class, $result);
        }
    }

    /**
     * @test
     */
    public function it_reuses_already_known_models()
    {
        $this->mockCountCall(1);
        $this->mockFirstPageCall(1);

        $this->search->toArray();

        $result = $this->search->toArray();

        $this->assertInstanceOf(Model::class, $result[0]);

        $this->mockBlogArticleCall(1);

        $this->assertInstanceOf(BlogArticle::class, $result[0]);
        $this->assertSame('Blog article 1 title', $result[0]->getTitle());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_query()
    {
        $this->mockCountCall(5, 'bacteria');
        $this->mockFirstPageCall(5, 'bacteria');

        $this->traverseAndSanityCheck($this->search->forQuery('bacteria'));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockCountCall(5, $query = '', $descendingOrder = true, ['subject']);
        $this->mockFirstPageCall(5, $query = '', $descendingOrder = true, ['subject']);

        $this->traverseAndSanityCheck($this->search->forSubject('subject'));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_type()
    {
        $this->mockCountCall(5, $query = '', $descendingOrder = true, $subjects = [], ['blog-article']);
        $this->mockFirstPageCall(5, $query = '', $descendingOrder = true, $subjects = [], ['blog-article']);

        $this->traverseAndSanityCheck($this->search->forType('blog-article'));
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering()
    {
        $this->mockCountCall(5);
        $this->search->count();

        $this->mockCountCall(4, 'bacteria');
        $this->assertSame(4, $this->search->forQuery('bacteria')->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering()
    {
        $this->mockCountCall(10);
        $this->mockFirstPageCall(10);
        $this->search->toArray();

        $this->mockCountCall(8, 'bacteria');
        $this->mockFirstPageCall(8, 'bacteria');
        $this->search->forQuery('bacteria')->toArray();
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        foreach ($calls as $call) {
            $this->mockSearchCall($call['page'], $call['per-page'], $total = 5);
        }

        foreach ($this->search->slice($offset, $length) as $i => $searchResult) {
            $this->assertInstanceOf(Model::class, $searchResult);
        }
    }

    public function sliceProvider() : array
    {
        // 3rd arguments have to be updated to describe the expected result
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
        $this->mockCountCall(3);
        $this->mockFirstPageCall(3);

        $map = function (Model $model) {
            return get_class($model);
        };

        $this->assertSame(
            [BlogArticle::class, BlogArticle::class, BlogArticle::class],
            $this->search->map($map)->toArray()
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $filter = function (Model $model) {
            return $model->getId() > 3;
        };

        $this->assertEquals(2, count($this->search->filter($filter)));
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $reduce = function (int $carry = null, Model $model) {
            return $carry + $model->getId();
        };

        $this->assertSame(115, $this->search->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockCountCall(5, $query = '', $descendingOrder = true, $subjects = [], $types = [], 'relevance');
        $this->mockFirstPageCall(5, $query = '', $descendingOrder = true, $subjects = [], $types = [], 'relevance');

        $this->traverseAndSanityCheck($this->search->sortBy('relevance'));
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockCountCall(10, $query = '', $descendingOrder = false);
        $this->mockFirstPageCall(10, $query = '', $descendingOrder = false);

        $this->traverseAndSanityCheck($this->search->reverse());
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->mockCountCall(10);

        $this->search->count();

        $this->assertSame(10, $this->search->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->mockCountCall(10);
        $this->mockFirstPageCall(10);
        $this->search->toArray();

        $this->mockFirstPageCall(10, $query = '', $descendingOrder = false);
        $this->search->reverse()->toArray();
    }

    private function mockCountCall(int $count, string $query = '', bool $descendingOrder = true, array $subjects = [], $types = [], $sort = 'relevance')
    {
        $this->mockSearchCall(1, 1, $count, $query, $descendingOrder, $subjects, $types, $sort);
    }

    private function mockFirstPageCall($total, ...$options)
    {
        $this->mockSearchCall(1, 100, $total, ...$options);
    }

    private function traverseAndSanityCheck(Search $search)
    {
        foreach ($search as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
        }
    }
}
