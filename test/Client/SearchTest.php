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
        $this->mockSearchCall(1, 100, 200);
        $this->mockSearchCall(2, 100, 200);

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
        $this->mockSearchCall(1, 100, 10);

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
        $this->mockSearchCall(1, 100, 1);

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
        $this->mockSearchCall(1, 100, 5, 'bacteria');

        foreach ($this->search->forQuery('bacteria') as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
        }
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_subject()
    {
        $this->mockCountCall(5, '', ['subject']);
        $this->mockSearchCall(1, 100, 5, '', true, ['subject']);

        foreach ($this->search->forSubject('subject') as $i => $model) {
            $this->assertInstanceOf(Model::class, $model);
        }
    }

    private function mockCountCall(int $count, string $query = '', array $subjects = [])
    {
        $this->mockSearchCall(1, 1, $count, $query, true, $subjects);
    }
}
