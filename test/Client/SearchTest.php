<?php

namespace test\eLife\ApiSdk\Client;

use BadMethodCallException;
use DateTimeImmutable;
use InvalidArgumentException;
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
    private $defaultOptions = [
        'for' => '',
        'descendingOrder' => true,
        'subject' => [],
        'type' => [],
        'sort' => 'relevance',
        'useDate' => 'default',
        'startDate' => null,
        'endDate' => null,
        'elifeAssessmentSignificance' => [],
    ];

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
        $this->expectCountCallContaining([], 200);
        $this->expectFirstPageCallContaining([], 200);
        $this->mockSearchCall($page = 2, $perPage = 100, $total = 200);

        $this->assertSame(200, $this->traverseAndSanityCheck($this->search));
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->expectCountCallContaining([], 10);

        $this->assertFalse($this->search->isEmpty());
        $this->assertSame(10, $this->search->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $this->expectCountCallContaining([], 10);
        $this->expectFirstPageCallContaining([], 10);

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
        $this->expectCountCallContaining(['for' => 'bacteria'], 5);
        $this->expectFirstPageCallContaining(['for' => 'bacteria'], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forQuery('bacteria')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->expectCountCallContaining(['subject' => ['neuroscience']], 5);
        $this->expectFirstPageCallContaining(['subject' => ['neuroscience']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forSubject('neuroscience')));
    }

    /**
     * @test
     */
    public function it_only_filters_by_the_same_subject_once()
    {
        $this->expectCountCallContaining(['subject' => ['biochemistry']], 5);
        $this->expectFirstPageCallContaining(['subject' => ['biochemistry']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forSubject('biochemistry', 'biochemistry')));
    }

    /**
     * @test
     */
    public function it_can_handle_a_sequence_of_multiple_calls_with_different_subjects()
    {
        $this->expectCountCallContaining(['subject' => ['biochemistry', 'neuroscience']], 5);
        $this->expectFirstPageCallContaining(['subject' => ['biochemistry', 'neuroscience']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forSubject('biochemistry')->forSubject('neuroscience')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_elife_assessment_significance()
    {
        $this->expectCountCallContaining(['elifeAssessmentSignificance' => ['important', 'useful']], 5);
        $this->expectFirstPageCallContaining(['elifeAssessmentSignificance' => ['important', 'useful']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forElifeAssessmentSignificance('important', 'useful')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_elife_assessment_strength()
    {
        $this->markTestIncomplete();
        $this->expectCountCallContaining(['elifeAssessmentStrength' => ['solid', 'incomplete']], 5);
        $this->expectFirstPageCallContaining(['elifeAssessmentStrength' => ['solid', 'incomplete']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forElifeAssessmentStrength('solid', 'incomplete')));
    }

    /**
     * @test
     */
    public function it_can_handle_a_sequence_of_multiple_calls_with_different_elife_assessment_significances()
    {
        $this->expectCountCallContaining(['elifeAssessmentSignificance' => ['important', 'useful']], 5);
        $this->expectFirstPageCallContaining(['elifeAssessmentSignificance' => ['important', 'useful']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forElifeAssessmentSignificance('important')->forElifeAssessmentSignificance('useful')));
    }

    /**
     * @test
     */
    public function it_only_filters_by_the_same_elife_assessment_significance_once()
    {
        $this->expectCountCallContaining(['elifeAssessmentSignificance' => ['important']], 5);
        $this->expectFirstPageCallContaining(['elifeAssessmentSignificance' => ['important']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forElifeAssessmentSignificance('important', 'important')));
        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forElifeAssessmentSignificance('important')->forElifeAssessmentSignificance('important')));
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering_by_elifeAssessment_significance()
    {
        $this->expectCountCallContaining([], 5);

        $this->search->count();
        
        $this->expectCountCallContaining([
            'elifeAssessmentSignificance' => ['important'],
        ], 3);

        $this->assertSame(3, $this->search->forElifeAssessmentSignificance('important')->count());
    }


    /**
     * @test
     */
    public function it_can_be_filtered_by_type()
    {
        $this->expectCountCallContaining(['type' => ['blog-article']], 5);
        $this->expectFirstPageCallContaining(['type' => ['blog-article']], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->forType('blog-article')));
    }

    /**
     * @test
     */
    public function it_can_use_published_dates()
    {
        $this->expectCountCallContaining(['sort' => 'date', 'useDate' => 'published'], 5);
        $this->expectFirstPageCallContaining(['sort' => 'date', 'useDate' => 'published'], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->sortBy('date')->useDate('published')));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_start_date()
    {
        $this->expectCountCallContaining(['startDate' => new DateTimeImmutable('2017-01-02')], 5);
        $this->expectFirstPageCallContaining(['startDate' => new DateTimeImmutable('2017-01-02')], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->startDate(new DateTimeImmutable('2017-01-02'))));
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_end_date()
    {
        $this->expectCountCallContaining(['endDate' => new DateTimeImmutable('2017-01-02')], 5);
        $this->expectFirstPageCallContaining(['endDate' => new DateTimeImmutable('2017-01-02')], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->endDate(new DateTimeImmutable('2017-01-02'))));
    }

    /**
     * @test
     */
    public function it_recounts_when_filtering()
    {
        $this->expectCountCallContaining([], 5);

        $this->search->count();

        $this->expectCountCallContaining(['for' => 'bacteria'], 4);
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
        $this->expectCountCallContaining([], 5);

        $oldTypes = $total($this->search->types());
        $oldSubjects = $total($this->search->subjects());

        $this->expectCountCallContaining(['for' => 'bacteria'], 4);
        $this->assertNotEquals($oldTypes, $total($this->search->forQuery('bacteria')->types()), 'Types are not being refreshed');
        $this->assertNotEquals($oldSubjects, $total($this->search->forQuery('bacteria')->subjects()), 'Subjects are not being refreshed');
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_filtering()
    {
        $this->expectCountCallContaining([], 10);

        $this->expectFirstPageCallContaining([], 10);
        $this->search->toArray();

        $this->expectCountCallContaining(['for' => 'bacteria'], 8);
        $this->expectFirstPageCallContaining(['for' => 'bacteria'], 8);
        $this->assertSame(8, $this->traverseAndSanityCheck($this->search->forQuery('bacteria')->toArray()));
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $this->expectCountCallContaining([], 5);

        $this->expectFirstPageCallContaining([], 5);

        $values = $this->search->prepend('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['foo', 'bar', '1', '2', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $this->expectCountCallContaining([], 5);
        $this->expectFirstPageCallContaining([], 5);

        $values = $this->search->append('foo', 'bar')->map($this->tidyValue());

        $this->assertSame(['1', '2', '3', '4', '5', 'foo', 'bar'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_dropped()
    {
        $this->expectCountCallContaining([], 5);
        $this->expectFirstPageCallContaining([], 5);

        $values = $this->search->drop(2)->map($this->tidyValue());

        $this->assertSame(['1', '2', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $this->expectCountCallContaining([], 5);
        $this->expectFirstPageCallContaining([], 5);

        $values = $this->search->insert(2, 'foo')->map($this->tidyValue());

        $this->assertSame(['1', '2', 'foo', '3', '4', '5'], $values->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $this->expectCountCallContaining([], 5);
        $this->expectFirstPageCallContaining([], 5);

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
        $this->expectCountCallContaining([], 3);
        $this->expectFirstPageCallContaining([], 3);

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
        $this->expectCountCallContaining([], 5);
        $this->expectFirstPageCallContaining([], 5);

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
        $this->expectCountCallContaining([], 5);
        $this->expectFirstPageCallContaining([], 5);

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
        $this->expectCountCallContaining(['sort' => 'relevance'], 5);
        $this->expectFirstPageCallContaining(['sort' => 'relevance'], 5);

        $this->assertSame(5, $this->traverseAndSanityCheck($this->search->sortBy('relevance')));
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $this->expectCountCallContaining(['descendingOrder' => false], 10);
        $this->expectFirstPageCallContaining(['descendingOrder' => false], 10);

        $this->assertSame(10, $this->traverseAndSanityCheck($this->search->reverse()));
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $this->expectCountCallContaining([], 10);

        $this->search->count();

        $this->assertSame(10, $this->search->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $this->expectCountCallContaining([], 10);
        $this->expectFirstPageCallContaining([], 10);
        $this->search->toArray();

        $this->expectFirstPageCallContaining(['descendingOrder' => false], 10);
        $this->assertSame(10, $this->traverseAndSanityCheck($this->search->reverse()->toArray()));
    }

    /**
     * @test
     */
    public function it_has_counters_for_types()
    {
        $this->expectCountCallContaining([], 10);
        $this->expectFirstPageCallContaining([], 10);

        $types = $this->search->types();
        foreach ($types as $type => $counter) {
            $this->assertIsString($type);
            $this->assertRegexp('/^[a-z-]+$/', $type);
            $this->assertGreaterThanOrEqual(0, $counter);
        }
    }

    /**
     * @test
     */
    public function it_has_counters_for_subjects()
    {
        $this->expectCountCallContaining([], 10);
        $this->expectFirstPageCallContaining([], 10);

        $subjects = $this->search->subjects();
        foreach ($subjects as $subject => $counter) {
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertGreaterThanOrEqual(0, $counter);
        }
    }

    private function expectCountCallContaining(array $options, int $count)
    {
        $actualOptions = array_merge($this->defaultOptions, $options);
        if (sizeof($actualOptions) > sizeof($this->defaultOptions)) {
            throw new InvalidArgumentException('Unexpected options, update default options and the mockSearchCall arguments.');
        }
        
        $this->mockSearchCall(1, 1, $count, ...array_values($actualOptions));
    }

    private function expectFirstPageCallContaining(array $options, int $count)
    {
        $actualOptions = array_merge($this->defaultOptions, $options);
        
        $this->mockSearchCall(1, 100, $count, ...array_values($actualOptions));
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
