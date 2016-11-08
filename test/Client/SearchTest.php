<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
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

        $this->assertSame(200, $this->traverseAndSanityCheck($this->search));
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockCountCall(10);

        $this->assertFalse($this->search->isEmpty());
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

        $this->assertSame(10, $this->traverseAndSanityCheck($array));
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $this->mockSearchCall(1, 1, 1);

        $this->assertTrue(isset($this->search[0]));
        $this->assertInstanceOf(Model::class, $this->search[0]);

        $this->mockNotFound(
            'search?for=&page=6&per-page=1&sort=relevance&order=desc',
            ['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)]
        );

        $this->assertFalse(isset($this->search[5]));
        $this->assertSame(null, $this->search[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $this->expectException(BadMethodCallException::class);

        $this->search[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_reuses_already_known_models()
    {
        $this->mockCountCall(1);
        $this->mockFirstPageCall(1);

        $existingModel = $this->search[0];

        $models = $this->search->toArray();

        $this->assertInstanceOf(Model::class, $models[0]);

        $this->mockArticleCall(1);
        $this->assertSame($existingModel, $models[0]);
        $this->assertSame('Article 1 title', $models[0]->getTitle());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_query()
    {
        $this->mockCountCall(5, 'bacteria');
        $this->mockFirstPageCall(5, 'bacteria');

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forQuery('bacteria')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockCountCall(5, $query = '', $descendingOrder = true, ['subject']);
        $this->mockFirstPageCall(5, $query = '', $descendingOrder = true, ['subject']);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forSubject('subject')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_type()
    {
        $this->mockCountCall(5, $query = '', $descendingOrder = true, $subjects = [], ['blog-article']);
        $this->mockFirstPageCall(5, $query = '', $descendingOrder = true, $subjects = [], ['blog-article']);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forType('blog-article')));
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
    public function it_refreshes_subject_and_types_when_filtering()
    {
        $total = function ($iterator) {
            $sum = 0;
            foreach ($iterator as $results) {
                $sum += $results;
            }

            return $sum;
        };
        $this->mockCountCall(5);
        $oldTypes = $total($this->search->types());
        $oldSubjects = $total($this->search->subjects());

        $this->mockCountCall(4, 'bacteria');
        $this->assertNotEquals($oldTypes, $total($this->search->forQuery('bacteria')->types()), 'Types are not being refreshed');
        $this->assertNotEquals($oldSubjects, $total($this->search->forQuery('bacteria')->subjects()), 'Subjects are not being refreshed');
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
        $this->assertSame(8, $this->traverseAndSanityCheck($this->search->forQuery('bacteria')->toArray()));
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

        $this->traverseAndSanityCheck($this->search->slice($offset, $length));
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
            [ArticlePoA::class, ArticleVoR::class, BlogArticle::class],
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
            return get_class($model) == BlogArticle::class;
        };

        $this->assertEquals(1, count($this->search->filter($filter)));
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $reduce = function (int $carry = null, Model $model) {
            return $carry + 1;
        };

        $this->assertSame(105, $this->search->reduce($reduce, 100)->wait());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $this->mockCountCall(5, $query = '', $descendingOrder = true, $subjects = [], $types = [], 'relevance');
        $this->mockFirstPageCall(5, $query = '', $descendingOrder = true, $subjects = [], $types = [], 'relevance');

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->sortBy('relevance')));
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->mockCountCall(10, $query = '', $descendingOrder = false);
        $this->mockFirstPageCall(10, $query = '', $descendingOrder = false);

        $this->assertSame(10, $this->traverseAndSanityCheck($this->search->reverse()));
    }

    /**
     * @test
     */
    public function it_reuses_models_when_reversed_or_in_general_resliced()
    {
        $this->mockCountCall(10, $query = '');
        $this->mockFirstPageCall(10, $query = '', $descendingOrder = true);
        $models = $this->search->toArray();

        $this->mockFirstPageCall(10, $query = '', $descendingOrder = false);
        foreach ($this->search->reverse() as $item) {
            $this->assertTrue(array_search($item, $models, $strict = true) !== false, 'Search item not found in previous items objects set');
        }
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
        $this->assertSame(10, $this->traverseAndSanityCheck($this->search->reverse()->toArray()));
    }

    /**
     * @test
     */
    public function it_has_counters_for_types()
    {
        $this->mockCountCall(10);
        $this->mockFirstPageCall(10);

        $types = $this->search->types();
        foreach ($types as $type => $counter) {
            $this->assertInternalType('string', $type);
            $this->assertRegexp('/^[a-z-]+$/', $type);
            $this->assertGreaterThanOrEqual(0, $counter);
        }
    }

    /**
     * @test
     */
    public function it_has_counters_for_subjects()
    {
        $this->mockCountCall(10);
        $this->mockFirstPageCall(10);

        $subjects = $this->search->subjects();
        foreach ($subjects as $subject => $counter) {
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertGreaterThanOrEqual(0, $counter);
        }
    }

    private function mockCountCall(int $count, string $query = '', bool $descendingOrder = true, array $subjects = [], $types = [], $sort = 'relevance')
    {
        $this->mockSearchCall(1, 1, $count, $query, $descendingOrder, $subjects, $types, $sort);
    }

    private function mockFirstPageCall($total, ...$options)
    {
        $this->mockSearchCall(1, 100, $total, ...$options);
    }

    private function traverseAndSanityCheck($search)
    {
        $count = 0;
        foreach ($search as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
            ++$count;
        }

        return $count;
    }
}
