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
        $this->mockSearchCall(1, 1, 200);
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
        $this->mockSearchCall(1, 1, 10);

        $this->assertSame(10, $this->search->count());
    }
}
