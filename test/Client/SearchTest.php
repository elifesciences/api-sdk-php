<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\SearchClient;
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
    use SlicingTestCase;

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
            'search?for=&page=6&per-page=1&sort=relevance&order=desc&use-date=default',
            ['Accept' => (string) new MediaType(SearchClient::TYPE_SEARCH, 2)]
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
    public function it_can_use_published_dates()
    {
        $this->mockCountCall(5, '', true, [], [], 'date', 'published');
        $this->mockFirstPageCall(5, '', true, [], [], 'date', 'published');

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->sortBy('date')->useDate('published')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_start_date()
    {
        $this->mockCountCall(5, '', true, [], [], 'relevance', 'default', new DateTimeImmutable('2017-01-02'));
        $this->mockFirstPageCall(5, '', true, [], [], 'relevance', 'default', new DateTimeImmutable('2017-01-02'));

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->startDate(new DateTimeImmutable('2017-01-02'))));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_end_date()
    {
        $this->mockCountCall(5, '', true, [], [], 'relevance', 'default', null, new DateTimeImmutable('2017-01-02'));
        $this->mockFirstPageCall(5, '', true, [], [], 'relevance', 'default', null, new DateTimeImmutable('2017-01-02'));

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->endDate(new DateTimeImmutable('2017-01-02'))));
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
     */
    public function it_can_be_prepended()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $values = $this->search->prepend('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['foo', 'bar', '1', '2', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $values = $this->search->append('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['1', '2', '3', '4', '5', 'foo', 'bar'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $values = $this->search->drop(2)->map($this->tidyValue());

        $this->assertSame(['1', '2', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $values = $this->search->insert(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->mockCountCall(5);
        $this->mockFirstPageCall(5);

        $values = $this->search->set(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '4', '5'], $values->toArray());
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
            return BlogArticle::class == get_class($model);
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

        $this->assertSame(105, $this->search->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_does_not_need_to_be_flattened()
    {
        $this->assertSame($this->search, $this->search->flatten());
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

    private function mockCountCall(int $count, ...$options)
    {
        $this->mockSearchCall(1, 1, $count, ...$options);
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
