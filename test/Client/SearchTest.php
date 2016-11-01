<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
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

    private function mockCountCall($count)
    {
        $this->mockSearchCall(1, 1, $count);
    }
}
