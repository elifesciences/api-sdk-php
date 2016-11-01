<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Collection\Sequence;
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
    public function it_can_be_traversed()
    {
        $this->mockCollectionListCall(1, 1, 200);
        $this->mockCollectionListCall(1, 100, 200);
        $this->mockCollectionListCall(2, 100, 200);

        foreach ($this->collections as $i => $collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertSame((string) $i, $collection->getId());
        }
    }
     */

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $this->mockSearchCall(1, 1, 10);

        $this->assertSame(10, $this->search->count());
    }
}
